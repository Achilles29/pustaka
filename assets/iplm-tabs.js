/* Progressive enhancement: without JavaScript every section remains readable. */
document.addEventListener('DOMContentLoaded',function(){
  document.querySelectorAll('[data-iplm-tabs]').forEach(function(root){
    const buttons=Array.from(root.querySelectorAll('[role="tab"]'));
    const panels=buttons.map(b=>document.getElementById(b.getAttribute('aria-controls')));
    if(!buttons.length||panels.some(p=>!p))return;
    function select(index,focus){buttons.forEach(function(b,i){const active=i===index;b.setAttribute('aria-selected',String(active));b.tabIndex=active?0:-1;panels[i].hidden=!active;});if(focus)buttons[index].focus();}
    buttons.forEach(function(button,i){button.addEventListener('click',()=>select(i,false));button.addEventListener('keydown',function(e){let next=i;if(e.key==='ArrowRight')next=(i+1)%buttons.length;else if(e.key==='ArrowLeft')next=(i-1+buttons.length)%buttons.length;else if(e.key==='Home')next=0;else if(e.key==='End')next=buttons.length-1;else return;e.preventDefault();select(next,true);});});
    select(Math.max(0,buttons.findIndex(b=>b.getAttribute('aria-selected')==='true')),false);
    const form=root.closest('form');if(form)form.addEventListener('invalid',function(e){const index=panels.findIndex(p=>p.contains(e.target));if(index>=0)select(index,false);},true);
  });
});
