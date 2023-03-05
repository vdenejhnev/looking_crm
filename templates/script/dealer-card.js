(function($) {
  const DealerCard = function(el, options) {
    this.el = el;
    this.options = options;

    this.fieldName = $('[data-field=name]', this.el);
    this.fieldCompanyName = $('[data-field="company.name"]', this.el);
    this.fieldCityName = $('[data-field="city.name"]', this.el);
    this.fieldPhone = $('[data-field=phone]', this.el);
    this.fieldEmail = $('[data-field=email]', this.el);

    $('[data-emit]', this.el).on('click', event => {
      event.preventDefault();
      const type = $(event.currentTarget).data('emit');
      $(this.el).trigger(`${type}.dealer`);
    });
  };

  const methods = {
    fill(dealer) {
      this.fieldName.text(dealer.name);
      this.fieldCompanyName.text(dealer.company?.name);
      this.fieldCityName.text(dealer.city?.name);
      this.fieldPhone.text(dealer.phone);
      this.fieldEmail.text(dealer.email);
    }
  };

  $.fn.dealerCard = function(method, ...options) {
    if (typeof method === 'string') {
      this.each((i, el) => {
        const $el = $(el);

        if (!$el.data('dealerCard')) {
          throw 'Trying to call method on uninitialized element: dealerCard';
        }

        methods[method].apply($el.data('dealerCard'), options);
      });
    } else {
      options = method;

      this.each((i, el) => {
        const $el = $(el);

        if (!$el.data('dealerCard')) {
          $el.data('dealerCard', new DealerCard($el, options));
        }
      });
    }

    return this;
  };

})(jQuery)