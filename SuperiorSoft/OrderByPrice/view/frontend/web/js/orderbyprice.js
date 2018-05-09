define(['uiComponent',    'jquery'],
    function(Component, $) {
        return Component.extend({
            initialize: function () {
                this._super();
                this.newValue = null;
                this.lowPrice = '';
                this.highPrice = '';
                this.sortPrice = 'asc';
                this.orders = null;
                this.errorMessage = '';
                this.observe(['people']);
                this.observe(['lowPrice']);
                this.observe(['highPrice']);
                this.observe(['sortPrice']);
                this.observe(['orders']);
                this.observe(['errorMessage']);
            },
            filter: function() {
                if (!this.validateForm('#orderByForm')) {
                    return;
                }
                var self = this;
                var data = {
                    lowPrice:this.lowPrice(),
                    highPrice:this.highPrice(),
                    sortPrice:this.sortPrice()
                }
                $('body').trigger('processStart');
                $.getJSON("/orderbyprice/ajax", data).done(
                    function(data) {
                        $('body').trigger('processStop');
                        self.orders(data.items);
                        self.errorMessage(data.errorMessage);
                    }
                ).fail(
                    function () {
                        $('body').trigger('processStop');
                    }
                );
            },
            validateForm: function (form) {
                return $(form).validation() && $(form).validation('isValid');
            },
        });
});