(function($) {

  const DealersList = function(options) {
    this.options = options;
    this.init();
  };

  for (const method in DataTable) {
    DealersList.prototype[method] = DataTable[method];
  }

  DealersList.prototype.editDealer = function(dealer) {
    $('input[name=id]', this.options.addForm).val(dealer.id);

    const popup = new Popup({
      afterClose: () => {
        this.showDealer(dealer.id);
      },
      beforeAppend: (root, content) => {
        $('input[name=company_name]', content).val(dealer.company.name);
        $('input[name=name]', content).val(dealer.name);
        $('input[name=city]', content).val(dealer.city.name);
        $('input[name=phone]', content).val(dealer.phone);
        $('input[name=email]', content).val(dealer.email);
        $('input[name=phone]', content).mask(
          '+7 (999) 999-99-99',
          { autoclear: false }
        );
      },
      content: this.options.addForm,
      title: 'Редактирование'
    });

    popup.show();

    $('.close-cross').on('click', function() {
      $('.popup-root').remove();
    });
  };

  DealersList.prototype.deleteDealer = function(dealer) {
    const popupChangePass = new Popup({
      beforeAppend: (root, content) => {},
      content: '<div><p><h3>Удаление дилера</h3></p><div class="popup-text">' + dealer.name + ' будет удален безвозвратно</div><div class="buttons-content"><button class="button-success">Оставить</button><button class="button-danger" id="delete_dealer">Удалить</button></div></div>',
    });
    $('.popup-root').remove();
    popupChangePass.show();

    $('.close-cross').on('click', function() {
      $('.popup-root').remove();
    });
    $('.button-success').on('click', function() {
      $('.popup-root').remove();
    });
    $('#delete_dealer').on('click', function() {
      $.post(
        '/ajax/admin/dealers.php',
        {
          action: 'delete-dealer',
          dealer_id: dealer.id
        },
        function() {},
        'json'
      );
      $('.popup-root').remove();
      
      document.location.reload();
    });
  };

  DealersList.prototype.changePassDealer = function(dealer) {
    $.post(
      '/ajax/admin/dealers.php',
      {
        action: 'change_pass-dealer',
        dealer_id: dealer.id,
        dealer_email: dealer.email
      },
      function(response) {
        const popupDeleteDealer = new Popup({
          beforeAppend: (root, content) => {},
          content: '<div><p>Пароль для доступа в систему отправлен на эл. почту дилера</p><p>' + dealer.email + '</p><button class="popup-button">Ок</button></div>',
        });
        $('.popup-root').remove();
        popupDeleteDealer.show();

        $('.close-cross').on('click', function() {
          $('.popup-root').remove();
        });
        $('.popup-button').on('click', function() {
          $('.popup-root').remove();
        });
      },
      'json'
    );
  };

  DealersList.prototype.disableDealer = function(dealer) {
    //console.log(dealer.enabled);

    $.post(
      '/ajax/admin/dealers.php',
      {
        action: 'disable-dealer',
        dealer_id: dealer.id,
        enabled: dealer.enabled
      },
      function(response) {
        if (dealer.enabled == 0) {
          dealer.enabled = 1;
          $('.disable-button').css('color', '#447EED');
          $('.enable-button').css('display', 'none');
          $('.disable-button').css('cursor', 'pointer');
          $('.disable-button').css('text-decoration', '');
        } else {
          dealer.enabled = 0;
          $('.disable-button').css('color', '#A4A4A4');
          $('.disable-button').css('cursor', 'default');
          $('.disable-button').css('text-decoration', 'none');
          $('.enable-button').css('display', 'block');
          $('.enable-button').css('cursor', 'pointer');
          $('.enable-button').css('margin', '0 5px');
        }
      },
      'json'
    );

    
  };

  DealersList.prototype.load = function(callback) {
    console.log($('input[name=email]', this.options.filter).val());
    $.post(
      '/ajax/admin/dealers.php',
      {
        action: 'list-dealers',
        limit: this.options.limit,
        page: this.options.page,
        filter_leads_from: $('input[name=leads_from]', this.options.filter).val(),
        filter_company: $('.filter-company', this.options.filter).data('val'),
        filter_leads_to: $('input[name=leads_to]', this.options.filter).val(),
        filter_name: $('input[name=name]', this.options.filter).val(),
        filter_city: $('input[name=city]', this.options.filter).val(),
        filter_email: $('input[name=email]', this.options.filter).val(),
        filter_phone: $('input[name=phone]', this.options.filter).val()
      },
      function(response) {
        console.log(response);
        if ('errors' in response && response.errors.length) {
          alert(response.errors.map(error => error.error).join("\n"));
        }

        if ('dealers' in response) {
          response.records = response.dealers;
          delete response.dealers;
          callback(response);
        }
      },
      'json'
    );
  };

  DealersList.prototype.makeTableRow = function(dealer) {
    if (dealer.leads_count == null) {
      dealer.leads_count = 0;
    }
    return $('<tr>')
      .data('id', dealer.id)
      .append($('<td>').text(dealer.name))
      .append($('<td>').text(dealer.company.name))
      .append($('<td>').text(dealer.city.name))
      .append($('<td>').text(dealer.phone))
      .append($('<td>').text(dealer.email))
      .append($('<td>').text(dealer.leads_count));
  };

  DealersList.prototype.onAddFormSubmit = function(event) {
    const self = this;
    const form = $(event.currentTarget);

    $.post(
      '/ajax/admin/dealers.php',
      form.serialize(),
      function(data) {
        //console.log(data);
        if ('errors' in data && data.errors.length) {
          DisplayFormErrors($(event.currentTarget), data.errors);
          return;
        }

        const dealerId = $('input[name=id]', form).val();

        if (dealerId) {
          form.data('popup').close();
          self.showDealer(dealerId);
        } else {
          document.location.reload();
        }
      },
      'json'
    );
  };

  DealersList.prototype.onRowClick = function(event) {
    this.showDealer($(event.currentTarget).data('id'));

  };

  DealersList.prototype.showDealer = function(dealerId) {
    const self = this;

    $.post('/ajax/admin/dealers.php', {action: 'get-dealer', id: dealerId}, response => {
      if (response.dealer.enabled == 1) {
        $('.disable-button').css('color', '#447EED');
        $('.enable-button').css('display', 'none');
        $('.disable-button').css('cursor', 'pointer');
        $('.disable-button').css('text-decoration', '');
      } else {
        $('.disable-button').css('color', '#A4A4A4');
        $('.disable-button').css('cursor', 'default');
        $('.disable-button').css('text-decoration', 'none');
        $('.enable-button').css('display', 'block');
        $('.enable-button').css('cursor', 'pointer');
        $('.enable-button').css('margin', '0 5px');
      }
      const popup = new Popup({
        beforeAppend: (root, content) => {
          $(content).dealerCard().dealerCard('fill', response.dealer);
          content.on('edit.dealer', event => {
            popup.close();
            self.editDealer(response.dealer)
          });
          content.on('delete.dealer', event => {
            popup.close();
            self.deleteDealer(response.dealer)
          });
          content.on('change-password.dealer', event => {
            self.changePassDealer(response.dealer);
          });
          content.on('disable.dealer', event => {
            $.post('/ajax/admin/dealers.php', {action: 'get-dealer', id: dealerId}, response => {
              self.disableDealer(response.dealer);
            }, 'json');
          }); 
        },
        content: self.options.dealerCard
      });
      popup.show();

      $('.close-cross').on('click', function() {
        $('.popup-root').remove();
      });
    }, 'json');
  };

  $(document).ready(function() {
    $('.leads-range input[type=text]').mask(
      '99.99.9999',
      { autoclear: false }
    );

    $('#company_select').on('click', function(){
      console.log('companies');
    });

  /* $('#filter_phone').mask(
      '+7 (999) 999-99-99',
      { autoclear: false }
    );

    $('#filter_phone').input(function(){
      DealersList.load();
      //console.log($(this).val());
    });*/

    const dealersList = new DealersList({
      addButton: $('#add-dealer'),
      addForm: $('.add-dealer-form'),
      addTitle: 'Новый дилер',
      dealerCard: $('.dealer-card'),
      filter: $('.data-table-filter'),
      limit: $('.dealers-count').val(),
      page: 1,
      pagerContainer: $('#dealers-list-pager'),
      recordsContainer: $('#dealers-list')
    });
  });
  

})(jQuery);
