function flashMsg(type,msg){
  if(type==='error'||type==='warning'){Swal.fire({icon:type,title:type==='error'?'Something went wrong':'Please note',text:msg,confirmButtonColor:'#14213d'});}
  else{Swal.fire({toast:true,position:'top-end',icon:type,title:msg,showConfirmButton:false,timer:3800,timerProgressBar:true});}
}
document.addEventListener('submit',function(e){
  const f=e.target;
  if(f.classList.contains('js-confirm')&&!f.dataset.ok){
    e.preventDefault();
    Swal.fire({title:'Please confirm',text:f.dataset.msg||'Do you want to continue?',icon:'question',showCancelButton:true,confirmButtonColor:'#14213d',confirmButtonText:'Yes, continue'})
      .then(r=>{if(r.isConfirmed){f.dataset.ok=1;f.submit();}});
  }
});
document.querySelectorAll('.js-review').forEach(b=>b.addEventListener('click',()=>{
  const need=b.dataset.need==='1';
  Swal.fire({title:b.dataset.title,input:'textarea',inputLabel:'Comments'+(need?' (required)':' (optional)'),showCancelButton:true,confirmButtonColor:'#14213d',confirmButtonText:'Confirm',
    inputValidator:v=>(need&&!v.trim())?'Please write a comment for the author.':undefined})
  .then(r=>{if(r.isConfirmed){document.getElementById('rvDecision').value=b.dataset.decision;document.getElementById('rvComments').value=r.value||'';document.getElementById('reviewForm').submit();}});
}));
document.querySelectorAll('.js-copy').forEach(b=>b.addEventListener('click',()=>{
  navigator.clipboard.writeText(b.dataset.url).then(()=>flashMsg('success','Link copied. Only authorized users can open it.'),()=>Swal.fire({title:'Copy this link',input:'text',inputValue:b.dataset.url}));
}));
const sbtn=document.getElementById('sbtn');const sb=document.getElementById('sb');if(sbtn&&sb){sbtn.addEventListener('click',()=>{const open=sb.classList.toggle('open');sbtn.setAttribute('aria-expanded',String(open));});document.addEventListener('keydown',e=>{if(e.key==='Escape'){sb.classList.remove('open');sbtn.setAttribute('aria-expanded','false');}});}
