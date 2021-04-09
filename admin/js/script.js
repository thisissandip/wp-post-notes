jQuery(document).ready(function ($) {
	let adminform = document.querySelector('#admin-post-notes-form');
	let allinputs = adminform.querySelectorAll('input');

	allinputs.forEach((item) => {
		item.addEventListener('change', () => {
			adminform.submit();
		});
	});
});
