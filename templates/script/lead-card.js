(function($) {
  const LeadCard = function(el, options) {
    this.el = el;
    this.options = options;

    this.fieldId = $('[data-field=id]', this.el);
    this.fieldStatus = $('.status', this.el);
    this.fieldDatetime = $('.datetime', this.el);
    this.fieldDealer = $('.dealer', this.el);
    this.fieldDealerCompany = $('.dealer-company', this.el);
    this.fieldInn = $('[data-field=inn]', this.el);
    this.fieldCompany = $('[data-field=company]', this.el);
    this.fieldName = $('[data-field=name]', this.el);
   // this.fieldPhone = $('[data-field=name]', this.el);
    $('[data-emit]', this.el).on('click', event => {
      event.preventDefault();
      const type = $(event.currentTarget).data('emit');
      $(this.el).trigger(`${type}.dealer`);
    });

    $('.close-cross').on('click', function() {
      $('.popup-root').remove();
    });
  };

  const methods = {
    fill(lead) {
      const created_at = new Date(lead.created_at);

      this.el.fillFields(Object.assign({}, lead, {
        id: UTIL.lead.formatId(lead.id),
        status: LEAD_STATUSES[lead.status].name,
        created_at: [
          UTIL.datetime.formatDate(created_at),
          UTIL.datetime.formatTime(created_at)
        ].join(' ')
      }));
    }
  };
  
  $.fn.leadCard = function(method, ...options) {
    if (typeof method === 'string') {
      this.each((i, el) => {
        const $el = $(el);

        if (!$el.data('leadCard')) {
          throw 'Trying to call method on uninitialized element: leadCard';
        }

        methods[method].apply($el.data('leadCard'), options);
      });
    } else {
      options = method;

      this.each((i, el) => {
        const $el = $(el);

        if (!$el.data('leadCard')) {
          $el.data('leadCard', new LeadCard($el, options));
        }
      });
    }

    return this;
  };

})(jQuery)