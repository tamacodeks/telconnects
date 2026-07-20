'use strict';
var notify = $.notify('<i class="fa fa-bell-o"></i><strong>Loading</strong> page Do not close this page...', {
    type: 'theme',
    allow_dismiss: true,
    delay: 2000,
    showProgressbar: true,
    timer: 300,
    placement: {
        from: 'top',
        align: 'right'
    },
    offset: {
        x: 18,
        y: 86
    },
    z_index: 1045,
    animate:{
        enter:'animated fadeInDown',
        exit:'animated fadeOutUp'
    }
});

setTimeout(function() {
    notify.update('message', '<i class="fa fa-bell-o"></i><strong>Loading</strong> Inner Data.');
}, 1000);
