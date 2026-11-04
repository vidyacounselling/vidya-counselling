
'use strict';
// Confirm the business phone and email before launch. No backend or libraries are required.
const CONTACT={whatsapp:'919920392160'};
const menu=document.querySelector('.menu'),nav=document.getElementById('navigation');
function closeMenu(){nav.classList.remove('open');menu.setAttribute('aria-expanded','false');}
menu.addEventListener('click',()=>{const open=nav.classList.toggle('open');menu.setAttribute('aria-expanded',String(open));});
nav.querySelectorAll('a').forEach(a=>a.addEventListener('click',closeMenu));
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeMenu();});
document.querySelectorAll('.service-link').forEach(a=>a.addEventListener('click',()=>{document.getElementById('service').value=a.dataset.service;}));
document.querySelectorAll('.mode-link').forEach(a=>a.addEventListener('click',()=>{document.getElementById('mode').value=a.dataset.mode;}));
document.querySelectorAll('[data-dialog]').forEach(el=>el.addEventListener('click',e=>{e.preventDefault();document.getElementById(el.dataset.dialog).showModal();}));
document.querySelectorAll('dialog').forEach(d=>{d.querySelector('.close').addEventListener('click',()=>d.close());d.addEventListener('click',e=>{if(e.target===d){const r=d.getBoundingClientRect();if(e.clientX<r.left||e.clientX>r.right||e.clientY<r.top||e.clientY>r.bottom)d.close();}});});
document.getElementById('enquiry').addEventListener('submit',e=>{e.preventDefault();const f=e.currentTarget,n=f.elements.name;if(!n.value.trim()){n.setCustomValidity('Please enter your name.');n.reportValidity();return;}n.setCustomValidity('');const message='Hello Vidya Counselling Centre, I would like to enquire about a consultation.\nName: '+n.value.trim()+'\nInterest: '+f.elements.service.value+'\nSession preference: '+f.elements.mode.value+'\nPlease share availability and session details.';const url='https://wa.me/'+CONTACT.whatsapp+'?text='+encodeURIComponent(message);const status=document.getElementById('status');status.replaceChildren(document.createTextNode('Your message is ready. Review it and press Send in WhatsApp. '));const retry=document.createElement('a');retry.href=url;retry.target='_blank';retry.rel='noopener noreferrer';retry.textContent='Open WhatsApp';status.append(retry);window.open(url,'_blank','noopener,noreferrer');});
document.querySelector('input[name="name"]').addEventListener('input',e=>e.target.setCustomValidity(''));
document.getElementById('year').textContent=new Date().getFullYear();
