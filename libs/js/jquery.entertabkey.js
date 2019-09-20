$.fn.enterTabKey = function (fnc) {
    return this.each(function () {
        $(this).keypress(function (ev) {
            var keycode = (ev.keyCode ? ev.keyCode : ev.which);
            if (keycode == '13' || keycode == '9') {
            	ev.preventDefault();
                fnc.call(this, ev, keycode);
            }
        })
    })
}