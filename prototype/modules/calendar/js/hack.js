$.widget("ui.dialog", $.ui.dialog, {
    _allowInteraction: function () {
        if ($(event.target).closest(".ui-dialog").length || $(event.target).closest(".qtip").length || $(event.target).closest(".ui-datepicker").length) {
            return true;
        }
    }
});