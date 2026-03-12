function startButtonLoading(btn){

btn.dataset.originalText = btn.innerHTML;

btn.disabled = true;

btn.innerHTML = `
<span class="loading-spinner"></span>
`;

}

function stopButtonLoading(btn){

btn.disabled = false;

btn.innerHTML = btn.dataset.originalText;

}