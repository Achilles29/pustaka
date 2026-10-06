<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Season 2 world state and its code-defined Chapter 1 conversation flow. */
class Learn_english_rpg_world_model extends CI_Model
{
    const MAP_CODE = 'season2_village';

    public function world($user_id, $map_code = self::MAP_CODE)
    {
        $map = $this->db->get_where('learn_english_rpg_world_maps', ['code' => $map_code, 'is_active' => 1])->row_array();
        if (! $map) return null;
        $map['layout'] = json_decode((string) $map['layout_json'], true) ?: [];
        unset($map['layout_json']);
        $map['npcs'] = $this->db->where(['map_id' => (int) $map['id'], 'is_active' => 1])->order_by('id')->get('learn_english_rpg_world_npcs')->result_array();
        foreach ($map['npcs'] as &$npc) {
            $npc['dialogue'] = json_decode((string) $npc['dialogue_json'], true) ?: [];
            // Keep the voice profile tied to the illustrated sprite quadrant.
            // The database predates this field, so it is derived by code and
            // remains backward-compatible with existing installations.
            $npc['gender'] = (in_array($npc['code'], ['gardener_eli', 'scout_ren', 'innkeeper_tessa', 'gate_orin'], true)) ? 'female' : 'male';
            unset($npc['dialogue_json']);
        }
        unset($npc);
        $map['hotspots'] = $this->story_hotspots();
        $quest = $this->db->where(['map_id' => (int) $map['id'], 'is_active' => 1])->order_by('id')->get('learn_english_rpg_world_quests')->row_array();
        if ($quest) {
            $quest['objective_codes'] = json_decode((string) $quest['objective_codes_json'], true) ?: [];
            unset($quest['objective_codes_json']);
        }
        $state = $this->state($user_id, $map);
        $quest_state = $quest ? $this->quest_state($user_id, $quest) : ['status' => 'not_started', 'progress' => 0, 'visited' => [], 'question' => null];
        $ending = $quest_state['status'] === 'claimed' ? $this->chapter_ending() : null;
        return ['map' => $map, 'quest' => $quest, 'state' => $state, 'quest_state' => $quest_state, 'ending' => $ending];
    }

    public function move($user_id, $direction, $map_code = self::MAP_CODE)
    {
        $world = $this->world($user_id, $map_code);
        if (! $world) return ['ok' => false, 'message' => 'The Season 2 world is not available yet.'];
        $direction = in_array($direction, ['up', 'down', 'left', 'right'], true) ? $direction : 'down';
        $state = $world['state']; $x = (int) $state['x']; $y = (int) $state['y'];
        if ($direction === 'up') $y--; elseif ($direction === 'down') $y++; elseif ($direction === 'left') $x--; else $x++;
        $walkable = $this->walkable($world['map'], $x, $y) && ! $this->npc_occupies($world['map']['npcs'], $x, $y);
        $where = ['user_id' => (int) $user_id, 'map_id' => (int) $world['map']['id']];
        $payload = ['direction' => $direction, 'last_seen_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')];
        if ($walkable) { $payload['x'] = $x; $payload['y'] = $y; $state['x'] = $x; $state['y'] = $y; }
        $this->db->where($where)->update('learn_english_rpg_world_player_states', $payload);
        $state['direction'] = $direction;
        return ['ok' => true, 'moved' => $walkable, 'state' => $state, 'nearby' => $this->nearby_npcs($world['map']['npcs'], $state)];
    }

    public function interact($user_id, $npc_id, $map_code = self::MAP_CODE)
    {
        $world = $this->world($user_id, $map_code);
        if (! $world || ! $world['quest']) return ['ok' => false, 'message' => 'The Season 2 quest is not available yet.'];
        $npc = $this->npc_by_id($world['map']['npcs'], $npc_id);
        if (! $npc) return ['ok' => false, 'message' => 'Villager not found.'];
        if (! $this->is_nearby($npc, $world['state'])) return ['ok' => false, 'message' => 'Move closer to the villager before starting a conversation.'];
        $quest = $world['quest']; $qs = $world['quest_state'];

        if ($qs['status'] === 'active' && ! empty($qs['question'])) {
            if (($qs['question']['npc_code'] ?? '') !== $npc['code']) {
                $pending = $this->npc_by_code($world['map']['npcs'], $qs['question']['npc_code'] ?? '');
                $pending_name = $pending['name'] ?? 'the next villager';
                $message = 'Please talk to '.$pending_name.' first. '.$pending_name.' is waiting for your answer.';
                return $this->dialogue_redirect($user_id, $world, $npc, $message, ['stage' => $qs['stage'] ?? null, 'target_npc' => $qs['question']['npc_code'] ?? null]);
            }
            $this->log_interaction($user_id, $world, $npc, 'dialogue_opened', ['stage' => $qs['stage'] ?? null]);
            return $this->conversation_response($world, $npc, 'The villager is waiting for your answer.');
        }

        if ($qs['status'] === 'active') {
            return $this->dialogue_redirect($user_id, $world, $npc, 'Please complete the current objective first, then return here. Follow the glowing objective on your quest panel.', ['stage' => $qs['stage'] ?? null]);
        }

        if ($qs['status'] === 'not_started') {
            if ($npc['code'] !== $quest['giver_npc_code']) return $this->dialogue_redirect($user_id, $world, $npc, 'Please talk to Mira first. She will explain the missing story sigils.', ['stage' => 'start', 'target_npc' => $quest['giver_npc_code']]);
            $this->save_quest_state($user_id, $quest, 'active', [], 'start');
            $this->log_interaction($user_id, $world, $npc, 'quest_started', ['stage' => 'start']);
            $world = $this->world($user_id, $map_code);
            return $this->conversation_response($world, $npc, 'Mira has a question for you. Choose the best answer to begin.');
        }

        if ($qs['status'] === 'completed' && $npc['code'] === $quest['giver_npc_code']) {
            return $this->claim_legacy_completion($user_id, $world, $npc, $map_code);
        }
        if ($qs['status'] === 'claimed' && $npc['code'] === $quest['giver_npc_code']) {
            $this->log_interaction($user_id, $world, $npc, 'dialogue_opened', ['stage' => 'complete']);
            return ['ok' => true, 'message' => 'Mira smiles. “Your story is now part of the village.”', 'npc' => $npc, 'quest_state' => $qs, 'state' => $world['state'], 'ending' => $this->chapter_ending()];
        }
        return ['ok' => true, 'message' => 'Explore the village and follow the quest steps.', 'npc' => $npc, 'quest_state' => $qs, 'state' => $world['state']];
    }

    public function answer_question($user_id, $npc_id, $answer, $map_code = self::MAP_CODE)
    {
        $world = $this->world($user_id, $map_code);
        if (! $world || ! $world['quest']) return ['ok' => false, 'message' => 'The Season 2 quest is not available yet.'];
        $qs = $world['quest_state']; $npc = $this->npc_by_id($world['map']['npcs'], $npc_id);
        if (! $npc || ! $this->is_nearby($npc, $world['state'])) return ['ok' => false, 'message' => 'Move next to the villager before answering.'];
        $stage = $qs['stage'] ?? null; $bank = $this->question_bank(); $question = $stage && isset($bank[$stage]) ? $bank[$stage] : null;
        if ($qs['status'] !== 'active' || ! $question || ($question['npc_code'] ?? '') !== $npc['code']) return ['ok' => false, 'message' => 'There is no active question for this villager.'];
        $answer_index = (int) $answer; $correct = isset($question['options'][$answer_index]) && $question['options'][$answer_index] === $question['correct'];
        if (! $correct) {
            $this->log_interaction($user_id, $world, $npc, 'question_answered', ['stage' => $stage, 'answer' => $answer_index, 'correct' => false, 'progress' => (int) $qs['progress']]);
            return ['ok' => true, 'correct' => false, 'message' => 'Not quite. Read the clue again and try once more.', 'feedback' => $question['wrong_feedback'] ?? 'Try again. Look for the key word in the villager’s clue.', 'npc' => $npc, 'question' => $this->public_question($stage), 'quest_state' => $qs, 'state' => $world['state']];
        }

        $visited = $qs['visited']; $next_stage = $question['next_stage']; $status = 'active';
        if (in_array($npc['code'], $world['quest']['objective_codes'], true) && ! in_array($npc['code'], $visited, true)) $visited[] = $npc['code'];
        $flags = $qs['flags']; if (! empty($question['sets_flag']) && ! in_array($question['sets_flag'], $flags, true)) $flags[] = $question['sets_flag'];
        $actions = $qs['actions']; $assists = $qs['assists']; $completed = false; $claimed = false; $reward = null; $ending = null;
        $this->db->trans_begin();
        $this->save_quest_state($user_id, $world['quest'], $status, $visited, $next_stage, $status, false, ['flags' => $flags, 'actions' => $actions, 'assists' => $assists]);
        $this->db->insert('learn_english_rpg_world_interactions', ['user_id' => (int) $user_id, 'map_id' => (int) $world['map']['id'], 'npc_id' => (int) $npc['id'], 'quest_id' => (int) $world['quest']['id'], 'interaction_type' => 'question_answered', 'payload_json' => json_encode(['stage' => $stage, 'correct' => true, 'next_stage' => $next_stage, 'progress' => $this->story_progress($next_stage, $status), 'flag' => $question['sets_flag'] ?? null])]);
        if ($this->db->trans_status() === false) { $this->db->trans_rollback(); return ['ok' => false, 'message' => 'The answer could not be saved.']; }
        $this->db->trans_commit();
        $fresh = $this->world($user_id, $map_code); $next_npc = $this->npc_by_code($fresh['map']['npcs'], $fresh['quest_state']['question']['npc_code'] ?? '');
        return ['ok' => true, 'correct' => true, 'message' => 'Correct! '.$this->objective_message($fresh['quest_state']), 'feedback' => $question['success'], 'npc' => $next_npc ?: $npc, 'question' => $fresh['quest_state']['question'], 'quest_state' => $fresh['quest_state'], 'state' => $fresh['state'], 'completed' => $completed, 'claimed' => $claimed, 'quest_id' => (int) $world['quest']['id'], 'reward' => $reward, 'ending' => $ending];
    }

    public function action($user_id, $action_code, $map_code = self::MAP_CODE)
    {
        $world = $this->world($user_id, $map_code); if (! $world || ! $world['quest']) return ['ok' => false, 'message' => 'The Season 2 quest is not available yet.'];
        $qs = $world['quest_state']; $bank = $this->action_bank(); $action = $bank[$action_code] ?? null; $hotspot = $action ? $this->hotspot_by_code($action['hotspot']) : null;
        if ($qs['status'] !== 'active' || ! $action || ($qs['stage'] ?? null) !== $action_code) return ['ok' => false, 'message' => $this->objective_message($qs)];
        if (! $hotspot || ! $this->point_nearby($hotspot, $world['state'])) return ['ok' => false, 'message' => 'Move closer to the glowing objective first.'];
        $next_stage = $action['next_stage']; $flags = $qs['flags']; $actions = $qs['actions']; $assists = $qs['assists'];
        if (! in_array($action_code, $actions, true)) $actions[] = $action_code;
        if (! empty($action['sets_flag']) && ! in_array($action['sets_flag'], $flags, true)) $flags[] = $action['sets_flag'];
        $claimed = $next_stage === 'complete'; $reward = null; $ending = null; $status = $claimed ? 'claimed' : 'active';
        if ($claimed) { $reward = ['xp' => (int) $world['quest']['reward_xp'], 'item_name' => $world['quest']['reward_item_name'], 'item_icon' => $world['quest']['reward_item_icon']]; $ending = $this->chapter_ending(); }
        $this->db->trans_begin();
        $this->save_quest_state($user_id, $world['quest'], $status, $qs['visited'], $claimed ? null : $next_stage, $status, $claimed, ['flags' => $flags, 'actions' => $actions, 'assists' => $assists]);
        if ($claimed) { $this->db->where(['user_id' => (int) $user_id, 'map_id' => (int) $world['map']['id']])->set('world_xp', 'world_xp+'.(int) $world['quest']['reward_xp'], false)->set('updated_at', date('Y-m-d H:i:s'))->update('learn_english_rpg_world_player_states'); $this->grant_item($user_id, $world['quest']); }
        $this->db->insert('learn_english_rpg_world_interactions', ['user_id' => (int) $user_id, 'map_id' => (int) $world['map']['id'], 'npc_id' => null, 'quest_id' => (int) $world['quest']['id'], 'interaction_type' => $claimed ? 'chapter_completed' : 'quest_action', 'payload_json' => json_encode(['action' => $action_code, 'next_stage' => $next_stage, 'progress' => $this->story_progress($next_stage, $status)])]);
        if ($this->db->trans_status() === false) { $this->db->trans_rollback(); return ['ok' => false, 'message' => 'The objective could not be saved.']; }
        $this->db->trans_commit(); $fresh = $this->world($user_id, $map_code);
        return ['ok' => true, 'message' => $claimed ? 'The Lantern Archive is restored. Chapter 1 is complete.' : ($action['success'] ?? 'Objective complete. Continue your quest.'), 'quest_state' => $fresh['quest_state'], 'state' => $fresh['state'], 'claimed' => $claimed, 'completed' => $claimed, 'quest_id' => (int) $world['quest']['id'], 'reward' => $reward, 'ending' => $ending];
    }

    public function help($user_id, $help_type, $map_code = self::MAP_CODE)
    {
        $world = $this->world($user_id, $map_code); if (! $world || ! $world['quest']) return ['ok' => false, 'message' => 'The Season 2 quest is not available yet.'];
        $qs = $world['quest_state']; $stage = $qs['stage'] ?? null; $source = $this->question_bank()[$stage] ?? ($this->action_bank()[$stage] ?? null);
        $help_type = in_array($help_type, ['hint', 'translate', 'explain', 'reveal'], true) ? $help_type : 'hint';
        if ($qs['status'] !== 'active' || ! $source) return ['ok' => false, 'message' => 'There is no active challenge that needs help.'];
        $help = $source['help'][$help_type] ?? null; if (! $help) return ['ok' => false, 'message' => 'This challenge has no extra help yet.'];
        $assists = $qs['assists']; if (! isset($assists[$stage]) || ! is_array($assists[$stage])) $assists[$stage] = [];
        if (! empty($assists[$stage][$help_type])) return ['ok' => true, 'message' => 'You already used this help for the current objective.', 'help' => $help, 'cost' => 0, 'charged' => 0, 'quest_state' => $qs, 'state' => $world['state']];
        $costs = ['hint' => 5, 'translate' => 8, 'explain' => 10, 'reveal' => 15]; $balance = (int) $world['state']['world_xp']; $charge = min($balance, $costs[$help_type]);
        $assists[$stage][$help_type] = true;
        $this->db->trans_begin();
        $this->save_quest_state($user_id, $world['quest'], 'active', $qs['visited'], $stage, 'active', false, ['flags' => $qs['flags'], 'actions' => $qs['actions'], 'assists' => $assists]);
        if ($charge > 0) $this->db->where(['user_id' => (int) $user_id, 'map_id' => (int) $world['map']['id']])->set('world_xp', 'GREATEST(world_xp-'.(int) $charge.',0)', false)->set('updated_at', date('Y-m-d H:i:s'))->update('learn_english_rpg_world_player_states');
        $this->db->insert('learn_english_rpg_world_interactions', ['user_id' => (int) $user_id, 'map_id' => (int) $world['map']['id'], 'npc_id' => null, 'quest_id' => (int) $world['quest']['id'], 'interaction_type' => 'help_used', 'payload_json' => json_encode(['stage' => $stage, 'help_type' => $help_type, 'cost' => $charge])]);
        if ($this->db->trans_status() === false) { $this->db->trans_rollback(); return ['ok' => false, 'message' => 'The help could not be saved.']; }
        $this->db->trans_commit(); $fresh = $this->world($user_id, $map_code);
        return ['ok' => true, 'message' => $charge > 0 ? 'Help unlocked. '.$charge.' XP used.' : 'Help unlocked. You had no XP to spend, but the lesson remains available.', 'help' => $help, 'cost' => $charge, 'charged' => $charge, 'quest_state' => $fresh['quest_state'], 'state' => $fresh['state']];
    }

    public function reset($user_id, $map_code = self::MAP_CODE)
    {
        $world = $this->world($user_id, $map_code); if (! $world) return ['ok' => false, 'message' => 'Season 2 world not found.'];
        $this->db->trans_begin();
        $this->db->where(['user_id' => (int) $user_id, 'map_id' => (int) $world['map']['id']])->delete('learn_english_rpg_world_player_states');
        if ($world['quest']) $this->db->where(['user_id' => (int) $user_id, 'quest_id' => (int) $world['quest']['id']])->delete('learn_english_rpg_world_player_quests');
        $this->db->where(['user_id' => (int) $user_id, 'map_id' => (int) $world['map']['id']])->delete('learn_english_rpg_world_interactions');
        $this->db->where(['user_id' => (int) $user_id, 'item_code' => 'lantern_sigil'])->delete('learn_english_rpg_inventory');
        if ($this->db->trans_status() === false) { $this->db->trans_rollback(); return ['ok' => false, 'message' => 'Season 2 reset failed.']; }
        $this->db->trans_commit(); return ['ok' => true, 'message' => 'Season 2 Chapter 1 progress has been reset.'];
    }

    public function progress_for_user($user_id)
    {
        $map = $this->db->get_where('learn_english_rpg_world_maps', ['code' => self::MAP_CODE, 'is_active' => 1])->row_array();
        if (! $map) return null;
        $map['layout'] = json_decode((string) $map['layout_json'], true) ?: [];
        unset($map['layout_json']);
        $quest = $this->db->where(['map_id' => (int) $map['id'], 'is_active' => 1])->order_by('id')->get('learn_english_rpg_world_quests')->row_array();
        if ($quest) { $quest['objective_codes'] = json_decode((string) $quest['objective_codes_json'], true) ?: []; unset($quest['objective_codes_json']); }
        $state = $this->db->get_where('learn_english_rpg_world_player_states', ['user_id' => (int) $user_id, 'map_id' => (int) $map['id']])->row_array();
        $quest_state = $quest ? $this->quest_state($user_id, $quest) : ['status' => 'not_started', 'progress' => 0, 'visited' => [], 'stage' => null, 'question' => null];
        $interactions = $this->db->select('i.id,i.interaction_type,i.payload_json,i.created_at,n.name npc_name')
            ->from('learn_english_rpg_world_interactions i')->join('learn_english_rpg_world_npcs n', 'n.id=i.npc_id', 'left')
            ->where(['i.user_id' => (int) $user_id, 'i.map_id' => (int) $map['id']])->order_by('i.created_at', 'DESC')->limit(150)->get()->result_array();
        foreach ($interactions as &$interaction) $interaction['payload'] = json_decode((string) $interaction['payload_json'], true) ?: [];
        unset($interaction);
        return ['map' => $map, 'state' => $state, 'quest' => $quest, 'quest_state' => $quest_state, 'interactions' => $interactions, 'ending' => $quest_state['status'] === 'claimed' ? $this->chapter_ending() : null];
    }

    private function question_bank()
    {
        return [
            'start' => ['npc_code' => 'elder_mira', 'prompt' => 'What are we searching for?', 'options' => ['Three story sigils', 'A lost crown', 'A silver boat'], 'correct' => 'Three story sigils', 'success' => 'Good listening. Mira asks you to find Pip and learn the first direction.', 'next_stage' => 'pip_intro', 'sets_flag' => 'chapter_1_started', 'help' => ['hint' => 'Mira says the archive needs three symbols.', 'translate' => 'We are searching for three story sigils. = Kita sedang mencari tiga sigil cerita.', 'explain' => 'A sigil is a symbol or seal. The missing objects are story sigils.', 'reveal' => 'Choose “Three story sigils”.']],
            'pip_intro' => ['npc_code' => 'courier_pip', 'prompt' => 'What did Pip lose?', 'options' => ['A storybook', 'A cooking pot', 'A blue ribbon'], 'correct' => 'A storybook', 'success' => 'Pip needs a careful listener. Find the lost book near the greenhouse.', 'next_stage' => 'storybook_search', 'sets_flag' => 'pip_storybook_started', 'help' => ['hint' => 'Pip was carrying a book with stories inside.', 'translate' => 'What did Pip lose? = Apa yang hilang dari Pip?', 'explain' => 'The object is a storybook, not a tool or a piece of clothing.', 'reveal' => 'Choose “A storybook”.']],
            'eli_intro' => ['npc_code' => 'gardener_eli', 'prompt' => 'What does Eli need first?', 'options' => ['Water for the seedlings', 'A new market sign', 'A silver crown'], 'correct' => 'Water for the seedlings', 'success' => 'The listening garden is thirsty. Follow Eli’s three simple instructions.', 'next_stage' => 'garden_repair_1', 'help' => ['hint' => 'Look at the young plants beside Eli.', 'translate' => 'The seedlings need water. = Bibit tanaman itu membutuhkan air.', 'explain' => 'Seedlings are young plants, so water helps them grow.', 'reveal' => 'Choose “Water for the seedlings”.']],
            'eli_root_check' => ['npc_code' => 'gardener_eli', 'prompt' => 'What did the root cellar protect?', 'options' => ['The old stories', 'The market coins', 'The bridge ropes'], 'correct' => 'The old stories', 'success' => 'Eli remembers the Root Sigil. Return to Mira and connect the word to the village.', 'next_stage' => 'mira_root_check', 'sets_flag' => 'clue_root', 'help' => ['hint' => 'Think about the archive and the word “root”.', 'translate' => 'The root cellar protected the old stories. = Gudang bawah tanah melindungi cerita-cerita lama.', 'explain' => 'The root clue is about origins and old stories, not market objects.', 'reveal' => 'Choose “The old stories”.']],
            'mira_root_check' => ['npc_code' => 'elder_mira', 'prompt' => 'Which word connects Eli’s clue to the village?', 'options' => ['Root', 'Bargain', 'Crossing'], 'correct' => 'Root', 'success' => 'The Root Sigil remembers where Lanternbrook came from. Now visit Nova at the market.', 'next_stage' => 'nova_intro', 'sets_flag' => 'root_understood', 'help' => ['hint' => 'The first sigil is about an origin.', 'translate' => 'Which word connects the clue? = Kata apa yang menghubungkan petunjuk itu?', 'explain' => 'Root means an origin or beginning, so it matches Eli’s clue.', 'reveal' => 'Choose “Root”.']],
            'nova_intro' => ['npc_code' => 'trader_nova', 'prompt' => 'What did Nova trade?', 'options' => ['A silver sigil', 'A basket of apples', 'A broken lantern'], 'correct' => 'A silver sigil', 'success' => 'Nova remembers a promise. Take the sealed note to Tessa at the inn.', 'next_stage' => 'deliver_note', 'help' => ['hint' => 'The object was bright and connected to a promise.', 'translate' => 'What did Nova trade? = Apa yang diperdagangkan Nova?', 'explain' => 'Nova traded the silver sigil, not an everyday market item.', 'reveal' => 'Choose “A silver sigil”.']],
            'tessa_question' => ['npc_code' => 'innkeeper_tessa', 'prompt' => 'Why did Tessa keep the sealed parcel?', 'options' => ['It carried an old promise', 'It was full of apples', 'It belonged to the bridge guard'], 'correct' => 'It carried an old promise', 'success' => 'The parcel carries a promise from the old keepers. Return it to Nova carefully.', 'next_stage' => 'parcel_search', 'sets_flag' => 'tessa_trusted', 'help' => ['hint' => 'Tessa says the parcel must reach someone who made a promise.', 'translate' => 'Why did Tessa keep the parcel? = Mengapa Tessa menyimpan paket itu?', 'explain' => 'She kept it because of its message, not because of its contents or size.', 'reveal' => 'Choose “It carried an old promise”.']],
            'nova_promise_check' => ['npc_code' => 'trader_nova', 'prompt' => 'What must a promise do?', 'options' => ['Be kept', 'Be hidden forever', 'Be sold at the market'], 'correct' => 'Be kept', 'success' => 'The Promise Sigil is about trust. Ren is waiting near the bridge trail.', 'next_stage' => 'ren_intro', 'sets_flag' => 'clue_promise', 'help' => ['hint' => 'A promise is something you agree to do.', 'translate' => 'A promise must be kept. = Sebuah janji harus ditepati.', 'explain' => 'To keep a promise means to do what you said you would do.', 'reveal' => 'Choose “Be kept”.']],
            'ren_intro' => ['npc_code' => 'scout_ren', 'prompt' => 'Where should you look first?', 'options' => ['Across the bridge', 'Inside the fountain', 'Behind the market roof'], 'correct' => 'Across the bridge', 'success' => 'Read the trail markers in order. The crossing clue is close.', 'next_stage' => 'trail_marker_1', 'help' => ['hint' => 'Ren points toward the bridge, not the village center.', 'translate' => 'Where should you look first? = Di mana kamu harus mencari terlebih dahulu?', 'explain' => 'Across means from one side to the other, which matches the bridge.', 'reveal' => 'Choose “Across the bridge”.']],
            'orin_warning' => ['npc_code' => 'gate_orin', 'prompt' => 'Why is the northern gate closed?', 'options' => ['The Hollow Echo is near', 'The festival is over', 'The bridge is being painted'], 'correct' => 'The Hollow Echo is near', 'success' => 'Orin warns you without closing the story. Return to Ren for the final trail clue.', 'next_stage' => 'ren_crossing_check', 'sets_flag' => 'hollow_echo_seen', 'help' => ['hint' => 'Listen for the name of the new threat.', 'translate' => 'Why is the gate closed? = Mengapa gerbang itu ditutup?', 'explain' => 'Orin is protecting the village from the Hollow Echo.', 'reveal' => 'Choose “The Hollow Echo is near”.']],
            'ren_crossing_check' => ['npc_code' => 'scout_ren', 'prompt' => 'What do the three trail markers form?', 'options' => ['A crossing path', 'A market list', 'A garden recipe'], 'correct' => 'A crossing path', 'success' => 'The Crossing Sigil points to a new road. Bram can repair the broken sigil case.', 'next_stage' => 'bram_intro', 'sets_flag' => 'clue_crossing', 'help' => ['hint' => 'Three markers guide travelers from one side to another.', 'translate' => 'The markers form a crossing path. = Penanda itu membentuk jalur penyeberangan.', 'explain' => 'A crossing is a place or path used to go across.', 'reveal' => 'Choose “A crossing path”.']],
            'bram_intro' => ['npc_code' => 'smith_bram', 'prompt' => 'What is the sigil case for?', 'options' => ['Holding the three sigils', 'Cooking a festival meal', 'Measuring the river'], 'correct' => 'Holding the three sigils', 'success' => 'Bram will repair the case after you gather three materials from the forge yard.', 'next_stage' => 'gather_wire', 'help' => ['hint' => 'The case is connected to the three objects you collected.', 'translate' => 'What is the case for? = Untuk apa wadah itu?', 'explain' => 'A case protects and holds important objects.', 'reveal' => 'Choose “Holding the three sigils”.']],
            'bram_repair' => ['npc_code' => 'smith_bram', 'prompt' => 'Which repair step comes first?', 'options' => ['Place the brass wire', 'Close the case', 'Light the archive'], 'correct' => 'Place the brass wire', 'success' => 'The case is ready. Return to Mira for the final story check.', 'next_stage' => 'mira_final_check', 'sets_flag' => 'case_repaired', 'help' => ['hint' => 'The wire must be placed before the case can close.', 'translate' => 'Which step comes first? = Langkah mana yang dilakukan pertama?', 'explain' => 'The repair order is place, fit, then close.', 'reveal' => 'Choose “Place the brass wire”.']],
            'mira_final_check' => ['npc_code' => 'elder_mira', 'prompt' => 'What did the three sigils teach?', 'options' => ['Remember, keep, and cross', 'Buy, hide, and shout', 'Run, sleep, and forget'], 'correct' => 'Remember, keep, and cross', 'success' => 'You connected every clue. Ask Orin for permission to approach the northern gate.', 'next_stage' => 'orin_gate', 'sets_flag' => 'final_check_passed', 'help' => ['hint' => 'Root remembers, promise is kept, and crossing opens a way.', 'translate' => 'What did the sigils teach? = Apa yang diajarkan oleh sigil-sigil itu?', 'explain' => 'The three ideas are the lessons of Root, Promise, and Crossing.', 'reveal' => 'Choose “Remember, keep, and cross”.']],
            'orin_gate' => ['npc_code' => 'gate_orin', 'prompt' => 'Which sentence politely asks to pass?', 'options' => ['May we pass, please?', 'Move away now!', 'Give us the gate!'], 'correct' => 'May we pass, please?', 'success' => 'Orin opens the final path. Place the three sigils in the archive gate.', 'next_stage' => 'place_sigils', 'help' => ['hint' => 'Use a polite request with “May we...?”', 'translate' => 'May we pass, please? = Bolehkah kami lewat?', 'explain' => 'May we...? is a polite way to ask for permission.', 'reveal' => 'Choose “May we pass, please?”.']],
        ];
    }

    private function action_bank()
    {
        return [
            'storybook_search' => ['hotspot' => 'storybook', 'next_stage' => 'eli_intro', 'sets_flag' => 'pip_storybook_completed', 'success' => 'You found Pip’s storybook. Eli is waiting in the listening garden.', 'help' => ['hint' => 'Look near the greenhouse, where Pip last read.', 'translate' => 'Find the lost storybook near the greenhouse. = Temukan buku cerita yang hilang di dekat rumah kaca.', 'explain' => 'The greenhouse is the glass building beside the garden.', 'reveal' => 'Inspect the glowing storybook marker.']],
            'garden_repair_1' => ['hotspot' => 'garden_water', 'next_stage' => 'garden_repair_2', 'success' => 'The seedlings are ready for the next step.', 'help' => ['hint' => 'Start with the watering can.', 'translate' => 'Water the seedlings. = Siram bibit tanaman.', 'explain' => 'Water is the first instruction Eli gives you.', 'reveal' => 'Inspect the watering can.']],
            'garden_repair_2' => ['hotspot' => 'garden_stone', 'next_stage' => 'garden_repair_3', 'success' => 'You moved the stone and found a small root mark.', 'help' => ['hint' => 'The flat stone is beside the garden path.', 'translate' => 'Move the stone. = Pindahkan batu itu.', 'explain' => 'Move means change the position of something.', 'reveal' => 'Inspect the flat garden stone.']],
            'garden_repair_3' => ['hotspot' => 'garden_cover', 'next_stage' => 'root_search', 'success' => 'The seedlings are protected. A root mark glows behind the greenhouse.', 'help' => ['hint' => 'The last garden step protects the young plants.', 'translate' => 'Cover the seedlings. = Tutupi bibit tanaman.', 'explain' => 'Cover means put something over another thing.', 'reveal' => 'Inspect the cloth cover.']],
            'root_search' => ['hotspot' => 'root_cellar', 'next_stage' => 'eli_root_check', 'success' => 'The root cellar opens. A memory of the old stories rises from below.', 'sets_flag' => 'root_cellar_open', 'help' => ['hint' => 'Follow the glowing root mark near the garden wall.', 'translate' => 'Open the root cellar. = Buka gudang bawah tanah.', 'explain' => 'A cellar is a room below a building, often used for storage.', 'reveal' => 'Inspect the root cellar door.']],
            'deliver_note' => ['hotspot' => 'tessa_note', 'next_stage' => 'tessa_question', 'success' => 'Tessa accepts the promise note and unlocks the inn counter.', 'help' => ['hint' => 'Tessa is waiting at the inn on the west side of the village.', 'translate' => 'Deliver the note to Tessa. = Antarkan catatan itu kepada Tessa.', 'explain' => 'Deliver means take something to the person or place it belongs to.', 'reveal' => 'Find the glowing note marker beside Tessa.']],
            'parcel_search' => ['hotspot' => 'sealed_parcel', 'next_stage' => 'nova_promise_check', 'success' => 'The sealed parcel is safe. Return it to Nova at the market.', 'help' => ['hint' => 'Look beside the inn counter after Tessa tells her story.', 'translate' => 'Find the sealed parcel. = Temukan paket yang disegel.', 'explain' => 'Sealed means closed so the contents stay private.', 'reveal' => 'Inspect the glowing parcel marker.']],
            'trail_marker_1' => ['hotspot' => 'trail_marker_north', 'next_stage' => 'trail_marker_2', 'success' => 'The north marker says to continue beside the river.', 'help' => ['hint' => 'Begin with the marker that names a direction.', 'translate' => 'Read the north trail marker. = Baca penanda jalur utara.', 'explain' => 'North is a direction on the map.', 'reveal' => 'Inspect the north marker.']],
            'trail_marker_2' => ['hotspot' => 'trail_marker_bridge', 'next_stage' => 'trail_marker_3', 'success' => 'The bridge marker points across the water.', 'help' => ['hint' => 'The second marker is close to the bridge path.', 'translate' => 'Read the bridge trail marker. = Baca penanda jalur jembatan.', 'explain' => 'A bridge lets people cross over water or a gap.', 'reveal' => 'Inspect the bridge marker.']],
            'trail_marker_3' => ['hotspot' => 'trail_marker_crossing', 'next_stage' => 'orin_warning', 'success' => 'The three markers form a crossing path. Orin is waiting at the northern stairs.', 'sets_flag' => 'trail_read', 'help' => ['hint' => 'The last marker completes the path where routes meet.', 'translate' => 'Read the crossing marker. = Baca penanda penyeberangan.', 'explain' => 'A crossing is the final idea in Ren’s trail.', 'reveal' => 'Inspect the crossing marker.']],
            'gather_wire' => ['hotspot' => 'material_wire', 'next_stage' => 'gather_glass', 'success' => 'The brass wire is flexible and bright.', 'help' => ['hint' => 'Bram keeps the wire on the small workbench.', 'translate' => 'Find the brass wire. = Temukan kawat kuningan.', 'explain' => 'Brass is a yellow-gold metal used for the case.', 'reveal' => 'Inspect the brass wire.']],
            'gather_glass' => ['hotspot' => 'material_glass', 'next_stage' => 'gather_wood', 'success' => 'The blue glass catches the light.', 'help' => ['hint' => 'Look for the blue piece near the forge window.', 'translate' => 'Find the blue glass. = Temukan kaca biru.', 'explain' => 'The blue glass will carry the Crossing Sigil’s light.', 'reveal' => 'Inspect the blue glass.']],
            'gather_wood' => ['hotspot' => 'material_wood', 'next_stage' => 'bram_repair', 'success' => 'The dry wood completes Bram’s material list.', 'help' => ['hint' => 'The last material is stacked beside the forge.', 'translate' => 'Find the dry wood. = Temukan kayu kering.', 'explain' => 'Dry wood is ready to use and will not make the case damp.', 'reveal' => 'Inspect the wood stack.']],
            'place_sigils' => ['hotspot' => 'archive_gate', 'next_stage' => 'complete', 'sets_flag' => 'gate_opened', 'success' => 'The three sigils glow together. The Lantern Archive is restored.', 'help' => ['hint' => 'Place the sigils in the order Root, Promise, Crossing.', 'translate' => 'Place the three sigils. = Letakkan ketiga sigil.', 'explain' => 'The story moves from origin, to trust, to a new path.', 'reveal' => 'Inspect the archive gate and place the sigils.']],
        ];
    }

    private function public_question($stage)
    {
        $bank = $this->question_bank(); if (! isset($bank[$stage])) return null;
        return ['stage' => $stage, 'npc_code' => $bank[$stage]['npc_code'], 'prompt' => $bank[$stage]['prompt'], 'options' => $bank[$stage]['options']];
    }

    private function story_hotspots()
    {
        return [
            ['code' => 'storybook', 'action_code' => 'storybook_search', 'name' => 'Lost storybook', 'type' => 'story', 'emoji' => '📖', 'x' => 8, 'y' => 2, 'description' => 'A small book glows near the greenhouse.'],
            ['code' => 'garden_water', 'action_code' => 'garden_repair_1', 'name' => 'Watering can', 'type' => 'action', 'emoji' => '💧', 'x' => 9, 'y' => 3, 'description' => 'Water the listening garden.'],
            ['code' => 'garden_stone', 'action_code' => 'garden_repair_2', 'name' => 'Flat garden stone', 'type' => 'action', 'emoji' => '🪨', 'x' => 8, 'y' => 3, 'description' => 'Move the stone beside the seedlings.'],
            ['code' => 'garden_cover', 'action_code' => 'garden_repair_3', 'name' => 'Seedling cover', 'type' => 'action', 'emoji' => '🍃', 'x' => 7, 'y' => 3, 'description' => 'Cover the young plants.'],
            ['code' => 'root_cellar', 'action_code' => 'root_search', 'name' => 'Root cellar door', 'type' => 'action', 'emoji' => '🚪', 'x' => 6, 'y' => 4, 'description' => 'Inspect the glowing root mark.'],
            ['code' => 'tessa_note', 'action_code' => 'deliver_note', 'name' => 'Promise note', 'type' => 'action', 'emoji' => '✉️', 'x' => 2, 'y' => 7, 'description' => 'Deliver the note at the inn.'],
            ['code' => 'sealed_parcel', 'action_code' => 'parcel_search', 'name' => 'Sealed parcel', 'type' => 'action', 'emoji' => '📦', 'x' => 4, 'y' => 7, 'description' => 'Collect the parcel Tessa protected.'],
            ['code' => 'trail_marker_north', 'action_code' => 'trail_marker_1', 'name' => 'North trail marker', 'type' => 'action', 'emoji' => '🪧', 'x' => 6, 'y' => 5, 'description' => 'Read the first trail marker.'],
            ['code' => 'trail_marker_bridge', 'action_code' => 'trail_marker_2', 'name' => 'Bridge trail marker', 'type' => 'action', 'emoji' => '🪧', 'x' => 11, 'y' => 5, 'description' => 'Read the marker beside the bridge.'],
            ['code' => 'trail_marker_crossing', 'action_code' => 'trail_marker_3', 'name' => 'Crossing marker', 'type' => 'action', 'emoji' => '🪧', 'x' => 14, 'y' => 6, 'description' => 'Read the final marker where paths meet.'],
            ['code' => 'material_wire', 'action_code' => 'gather_wire', 'name' => 'Brass wire', 'type' => 'action', 'emoji' => '〰️', 'x' => 10, 'y' => 7, 'description' => 'Gather the wire from Bram’s workbench.'],
            ['code' => 'material_glass', 'action_code' => 'gather_glass', 'name' => 'Blue glass', 'type' => 'action', 'emoji' => '🔷', 'x' => 11, 'y' => 7, 'description' => 'Gather the blue glass.'],
            ['code' => 'material_wood', 'action_code' => 'gather_wood', 'name' => 'Dry wood', 'type' => 'action', 'emoji' => '🪵', 'x' => 12, 'y' => 7, 'description' => 'Gather the dry wood stack.'],
            ['code' => 'archive_gate', 'action_code' => 'place_sigils', 'name' => 'Lantern Archive gate', 'type' => 'action', 'emoji' => '🏮', 'x' => 10, 'y' => 2, 'description' => 'Place the three restored sigils.'],
        ];
    }

    private function hotspot_by_code($code)
    {
        foreach ($this->story_hotspots() as $hotspot) if ($hotspot['code'] === (string) $code) return $hotspot;
        return null;
    }

    private function point_nearby(array $point, array $state)
    {
        return abs((int) $point['x'] - (int) $state['x']) + abs((int) $point['y'] - (int) $state['y']) <= 1;
    }

    private function story_objectives()
    {
        return [
            ['code' => 'arrival', 'label' => 'The Darkened Archive', 'short' => 'Meet Mira, Pip, and recover the lost storybook.'],
            ['code' => 'root', 'label' => 'The Root Remembers', 'short' => 'Help Eli repair the garden and find the Root clue.'],
            ['code' => 'promise', 'label' => 'A Promise in the Market', 'short' => 'Carry the promise between Nova and Tessa.'],
            ['code' => 'crossing', 'label' => 'The Crossing Trail', 'short' => 'Read Ren’s markers and face Orin’s warning.'],
            ['code' => 'repair', 'label' => 'The Broken Case', 'short' => 'Gather materials and repair the sigil case with Bram.'],
            ['code' => 'gate', 'label' => 'The Northern Gate', 'short' => 'Pass Mira’s final check and restore the archive.'],
        ];
    }

    private function stage_objective($stage)
    {
        $groups = [
            ['start', 'pip_intro', 'storybook_search'],
            ['eli_intro', 'garden_repair_1', 'garden_repair_2', 'garden_repair_3', 'root_search', 'eli_root_check', 'mira_root_check'],
            ['nova_intro', 'deliver_note', 'tessa_question', 'parcel_search', 'nova_promise_check'],
            ['ren_intro', 'trail_marker_1', 'trail_marker_2', 'trail_marker_3', 'orin_warning', 'ren_crossing_check'],
            ['bram_intro', 'gather_wire', 'gather_glass', 'gather_wood', 'bram_repair'],
            ['mira_final_check', 'orin_gate', 'place_sigils', 'complete'],
        ];
        foreach ($groups as $index => $group) if (in_array((string) $stage, $group, true)) return $this->story_objectives()[$index];
        return $this->story_objectives()[0];
    }

    private function story_progress($stage, $status = 'active')
    {
        if ($status === 'claimed' || $stage === 'complete') return 6;
        $objective = $this->stage_objective($stage); foreach ($this->story_objectives() as $index => $row) if ($row['code'] === $objective['code']) return $index;
        return 0;
    }

    private function objective_message(array $quest_state)
    {
        if (($quest_state['status'] ?? '') === 'claimed') return 'Chapter 1 is complete. The path to Whispering Grove is open.';
        $objective = $quest_state['objective'] ?? $this->stage_objective($quest_state['stage'] ?? 'start');
        return $objective['short'] ?? 'Follow the current quest objective.';
    }

    private function quest_state($user_id, array $quest)
    {
        $row = $this->db->get_where('learn_english_rpg_world_player_quests', ['user_id' => (int) $user_id, 'quest_id' => (int) $quest['id']])->row_array();
        if (! $row) return ['status' => 'not_started', 'progress' => 0, 'total_objectives' => 6, 'visited' => [], 'flags' => [], 'actions' => [], 'assists' => [], 'stage' => null, 'objective' => $this->story_objectives()[0], 'question' => null];
        $decoded = json_decode((string) $row['visited_json'], true) ?: [];
        $visited = isset($decoded['visited']) && is_array($decoded['visited']) ? $decoded['visited'] : (is_array($decoded) ? $decoded : []);
        $stage = $decoded['stage'] ?? null;
        $flags = isset($decoded['flags']) && is_array($decoded['flags']) ? $decoded['flags'] : [];
        $actions = isset($decoded['actions']) && is_array($decoded['actions']) ? $decoded['actions'] : [];
        $assists = isset($decoded['assists']) && is_array($decoded['assists']) ? $decoded['assists'] : [];
        // Migrate a quest started by the earlier one-way prototype into the
        // new conversation sequence without losing its collected clues.
        $known_stages = array_merge(array_keys($this->question_bank()), array_keys($this->action_bank()));
        if ($row['status'] === 'active' && (! $stage || ! in_array($stage, $known_stages, true))) {
            if (in_array('scout_ren', $visited, true)) $stage = 'ren_crossing_check';
            elseif (in_array('trader_nova', $visited, true)) $stage = 'ren_intro';
            elseif (in_array('gardener_eli', $visited, true)) $stage = 'nova_intro';
            else $stage = 'start';
        }
        $status = $row['status']; $objective = $this->stage_objective($stage ?: 'start');
        return ['status' => $status, 'progress' => $status === 'claimed' ? 6 : $this->story_progress($stage ?: 'start', $status), 'total_objectives' => 6, 'visited' => $visited, 'flags' => $flags, 'actions' => $actions, 'assists' => $assists, 'stage' => $stage, 'objective' => $objective, 'question' => $stage ? $this->public_question($stage) : null];
    }

    private function save_quest_state($user_id, array $quest, $status, array $visited, $stage, $completed_status = null, $claimed = false, array $meta = [])
    {
        $payload = ['status' => $status, 'progress' => $this->story_progress($stage ?: 'complete', $status), 'visited_json' => json_encode(['visited' => array_values($visited), 'stage' => $stage, 'flags' => array_values($meta['flags'] ?? []), 'actions' => array_values($meta['actions'] ?? []), 'assists' => $meta['assists'] ?? []], JSON_UNESCAPED_UNICODE), 'updated_at' => date('Y-m-d H:i:s')];
        if ($completed_status === 'claimed') { $payload['completed_at'] = date('Y-m-d H:i:s'); $payload['claimed_at'] = date('Y-m-d H:i:s'); }
        $where = ['user_id' => (int) $user_id, 'quest_id' => (int) $quest['id']];
        $exists = $this->db->get_where('learn_english_rpg_world_player_quests', $where)->row_array();
        if ($exists) $this->db->where('user_id', (int) $user_id)->where('quest_id', (int) $quest['id'])->update('learn_english_rpg_world_player_quests', $payload);
        else $this->db->insert('learn_english_rpg_world_player_quests', $where + $payload + ['started_at' => date('Y-m-d H:i:s')]);
    }

    private function conversation_response(array $world, array $npc, $message)
    {
        return ['ok' => true, 'message' => $message, 'npc' => $npc, 'question' => $world['quest_state']['question'], 'quest_state' => $world['quest_state'], 'state' => $world['state']];
    }

    /**
     * Keep every nearby villager conversational, even when the story is
     * waiting for another NPC. The player gets a natural in-world redirect
     * instead of a silent/failed interaction.
     */
    private function dialogue_redirect($user_id, array $world, array $npc, $message, array $payload = [])
    {
        $this->log_interaction($user_id, $world, $npc, 'dialogue_redirected', $payload + ['message' => $message]);
        return [
            'ok' => true,
            'message' => $message,
            'dialogue_line' => $message,
            'npc' => $npc,
            'question' => null,
            'quest_state' => $world['quest_state'],
            'state' => $world['state'],
        ];
    }

    private function claim_legacy_completion($user_id, array $world, array $npc, $map_code)
    {
        $this->db->where(['user_id' => (int) $user_id, 'quest_id' => (int) $world['quest']['id']])->update('learn_english_rpg_world_player_quests', ['status' => 'claimed', 'claimed_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
        $this->db->where(['user_id' => (int) $user_id, 'map_id' => (int) $world['map']['id']])->set('world_xp', 'world_xp+'.(int) $world['quest']['reward_xp'], false)->update('learn_english_rpg_world_player_states');
        $this->grant_item($user_id, $world['quest']); $fresh = $this->world($user_id, $map_code);
        return ['ok' => true, 'message' => 'Chapter 1 complete. The next path is now open.', 'npc' => $npc, 'quest_state' => $fresh['quest_state'], 'state' => $fresh['state'], 'completed' => true, 'claimed' => true, 'reward' => ['xp' => (int) $world['quest']['reward_xp'], 'item_name' => $world['quest']['reward_item_name'], 'item_icon' => $world['quest']['reward_item_icon']], 'ending' => $this->chapter_ending()];
    }

    private function chapter_ending() { return ['title' => 'Chapter 1 Complete', 'message' => 'The three sigils glow together. Mira restores the Lantern Archive, and the village remembers your kindness.', 'next' => 'The Hollow Echo has heard your words. A new road to Whispering Grove is now open.']; }
    private function state($user_id, array $map)
    {
        $where = ['user_id' => (int) $user_id, 'map_id' => (int) $map['id']];
        $row = $this->db->get_where('learn_english_rpg_world_player_states', $where)->row_array();
        if (! $row) {
            $this->db->insert('learn_english_rpg_world_player_states', $where + ['x' => (int) $map['start_x'], 'y' => (int) $map['start_y'], 'direction' => 'down', 'world_xp' => 0]);
            $row = $this->db->get_where('learn_english_rpg_world_player_states', $where)->row_array();
        } elseif (! $this->walkable($map, (int) $row['x'], (int) $row['y']) || $this->npc_occupies($map['npcs'] ?? [], (int) $row['x'], (int) $row['y'])) {
            $this->db->where($where)->update('learn_english_rpg_world_player_states', ['x' => (int) $map['start_x'], 'y' => (int) $map['start_y'], 'updated_at' => date('Y-m-d H:i:s')]);
            $row['x'] = (int) $map['start_x']; $row['y'] = (int) $map['start_y'];
        }
        return $row ?: ($where + ['x' => (int) $map['start_x'], 'y' => (int) $map['start_y'], 'direction' => 'down', 'world_xp' => 0]);
    }
    private function walkable(array $map, $x, $y) { if ($x < 0 || $y < 0 || $x >= (int) $map['width'] || $y >= (int) $map['height']) return false; $row = (string) ($map['layout'][$y] ?? ''); $tile = $row[$x] ?? '#'; return $tile !== '#' && $tile !== 'T' && $tile !== 'B'; }
    private function npc_occupies(array $npcs, $x, $y)
    {
        foreach ($npcs as $npc) if ((int) $npc['x'] === (int) $x && (int) $npc['y'] === (int) $y) return true;
        return false;
    }
    private function is_nearby(array $npc, array $state) { return abs((int) $npc['x'] - (int) $state['x']) + abs((int) $npc['y'] - (int) $state['y']) <= 1; }
    private function nearby_npcs(array $npcs, array $state) { $nearby = []; foreach ($npcs as $npc) if ($this->is_nearby($npc, $state)) $nearby[] = (int) $npc['id']; return $nearby; }
    private function npc_by_id(array $npcs, $id) { foreach ($npcs as $npc) if ((int) $npc['id'] === (int) $id) return $npc; return null; }
    private function npc_by_code(array $npcs, $code) { foreach ($npcs as $npc) if ($npc['code'] === $code) return $npc; return null; }
    private function log_interaction($user_id, array $world, array $npc, $type, array $payload = [])
    {
        $this->db->insert('learn_english_rpg_world_interactions', ['user_id' => (int) $user_id, 'map_id' => (int) $world['map']['id'], 'npc_id' => (int) $npc['id'], 'quest_id' => (int) ($world['quest']['id'] ?? 0), 'interaction_type' => (string) $type, 'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE)]);
    }
    private function grant_item($user_id, array $quest) { $where = ['user_id' => (int) $user_id, 'item_code' => $quest['reward_item_code']]; $item = $this->db->get_where('learn_english_rpg_inventory', $where)->row_array(); if ($item) { $this->db->where('id', (int) $item['id'])->set('quantity', 'quantity+1', false)->update('learn_english_rpg_inventory'); return; } $this->db->insert('learn_english_rpg_inventory', $where + ['item_name' => $quest['reward_item_name'], 'item_icon' => $quest['reward_item_icon'], 'item_type' => 'artifact', 'quantity' => 1]); }
}
