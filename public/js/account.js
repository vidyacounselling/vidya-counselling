'use strict';
document.querySelectorAll('form').forEach(form=>form.addEventListener('submit',event=>{if(form.checkValidity()&&event.submitter){event.submitter.setAttribute('aria-busy','true');setTimeout(()=>{event.submitter.disabled=true;event.submitter.textContent='Signing in…'},0)}}));
document.querySelectorAll('[data-toggle-password]').forEach(button => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.togglePassword);
        const visible = input.type === 'password';
        input.type = visible ? 'text' : 'password';
        button.textContent = visible ? 'Hide' : 'Show';
        button.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
        button.setAttribute('aria-pressed', String(visible));
    });
});
