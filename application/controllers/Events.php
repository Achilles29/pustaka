<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Events extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Event_model');
	}

	public function index()
	{
		$this->require_permission('events.index', 'view');
		$this->render('events/index', [
			'title' => 'Event Literasi',
			'stats' => $this->Event_model->stats(),
			'events' => $this->Event_model->get_events(),
		]);
	}
}
