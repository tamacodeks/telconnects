var myElement = document.getElementById('simple-bar');
if (myElement && !myElement.closest('[data-v2-sidebar]')) {
    new SimpleBar(myElement, { autoHide: true });
}
