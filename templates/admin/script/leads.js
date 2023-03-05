(function($) {

  const LeadsList = function(options) {
    this.options = options;
    this.init();
  };

  for (const method in DataTable) {
    LeadsList.prototype[method] = DataTable[method];
  }

  LeadsList.prototype.formatLeadId = function(id) {
    return '#' + ('0'.repeat(6 - ('' + id).length)) + id;
  };

  LeadsList.prototype.doneLead = function(lead) {
    console.log(lead);
    const self = this;
    $('.popup-root').remove();

    const popupDoneLead = new Popup({
      content: self.options.doneLead
    });

    $('[data-field="id"]').text(self.formatLeadId(lead.id));
    popupDoneLead.show();

    $('.button-success').on('click', function() {
      $.post(
        '/ajax/admin/leads.php',
        {
          action: 'done-lead',
          id: lead.id
        },
        function() {},
        'json'
      );
      $('.popup-root').remove();
      
      document.location.reload();
    });

    $('.close-cross').on('click', function() {
      $('.popup-root').remove();
    });

    $('.button-cancel').on('click', function() {
      $('.popup-root').remove();
    });
  };

  
  LeadsList.prototype.deleteLead = function(lead) {
    console.log(lead);
    const self = this;
    $('.popup-root').remove();

    const popupDeleteLead = new Popup({
      content: self.options.deleteLead
    });

    $('[data-field="id"]').text(self.formatLeadId(lead.id));
    popupDeleteLead.show();

    $('.close-cross').on('click', function() {
      $('.popup-root').remove();
    });

    $('.button-success').on('click', function() {
      $('.popup-root').remove();
    });

    $('.button-danger').on('click', function() {
      $.post(
        '/ajax/admin/leads.php',
        {
          action: 'delete-lead',
          id: lead.id
        },
        function() {},
        'json'
      );
      $('.popup-root').remove();
      
      document.location.reload();
    });
  };


  LeadsList.prototype.load = function(callback) {
    $.post(
      '/ajax/admin/leads.php',
      {
        action: 'list-leads',
        limit: this.options.limit,
        page: this.options.page,

        filter_status: $('.filter-status', this.options.recordsContainer).data('val'),
        filter_id: $('input[name=id]', this.options.recordsContainer).val(),
        filter_inn: $('input[name=inn]', this.options.recordsContainer).val(),
        filter_company_name: $('input[name=company_name]', this.options.recordsContainer).val(),
        filter_phone: $('input[name=phone]', this.options.recordsContainer).val(),
        filter_email: $('input[name=email]', this.options.recordsContainer).val(),
        filter_inn_sort: $('.inn-sort').data('sort'),
        filter_date_sort: $('.date-sort').data('sort'),
        filter_dealer: $('input[name=dealer]', this.options.recordsContainer).val(),
        filter_dealer_company: $('.filter-dealer-company', this.options.recordsContainer).data('val'),
        filter_leads_from: $('input[name=leads_from]').val(),
        filter_leads_to: $('input[name=leads_to]').val(),
      },
      function(response) {
        console.log(response);
        if ('errors' in response && response.errors.length) {
          console.log(response.errors);
        }

        if ('leads' in response) {
          response.records = response.leads;
          delete response.leads;
          callback(response);
        }
      },
      'json'
    );
  };

   LeadsList.prototype.editLead = function(lead) {
    const self = this;
    var item;
    var lead_date = '';
    var lead_time = '';
    var inn_date = '';
    var inn_time = '';

    if (lead.inn_added_at != null) {
      inn_date = lead.inn_added_at.split(' ')[0];
      inn_time = (lead.inn_added_at.split(' ')[1]).substr(0, 5);
    }

    if (lead.created_at != null) {
      lead_date = lead.created_at.split(' ')[0];
      lead_time = (lead.created_at.split(' ')[1]).substr(0, 5);
    }
    console.log(lead);

    var options = new Map ([
      ['lead_date', lead_date],
      ['lead_time', lead_time],
      ['name', lead.name],
      ['inn', lead.inn],
      ['inn_date', inn_date],
      ['inn_time', inn_time],
      ['company_name', lead.company_name],
      ['city', lead.city],
      ['comment', lead.comment]
    ]);

    const popup = new Popup({
      beforeAppend: (root, content) => {  
        $.post(
          '/ajax/admin/dealers.php',
          {
            action: 'get-dealer',
            id: lead.user_id,
          },
          function(data) {
            if (data.dealer != false) {
              $('.edit-lead-card .dealer').text(data.dealer.name);
              $('.edit-lead-card .dealer-company').text(data.dealer.company.name);
            }
          },
          'json'
        );

        $('input[name=phone]', content).mask(
          '+7 (999) 999-99-99',
          { autoclear: false }
        );
        $('input[name=phone2]', content).mask(
          '+7 (999) 999-99-99',
          { autoclear: false }
        );
        $('input[name=phone3]', content).mask(
          '+7 (999) 999-99-99',
          { autoclear: false }
        );
      },
      content: this.options.editForm,
    });

    $('.id', self.options.editForm).text(self.formatLeadId(lead.id));
    $('.status', self.options.editForm).html('<div class="status-color" style="background-color:' + LEAD_STATUSES[lead.status]?.color + ';"></div>' + LEAD_STATUSES[lead.status]?.name);
    $('input[name=lead_date]',self.options.editForm).val(lead_date);
    $('input[name=lead_time]',self.options.editForm).val(lead_time);
    $('input[name=company_name]', self.options.editForm).val(lead.company_name);
    $('input[name=name]', self.options.editForm).val(lead.name);
    $('input[name=city]', self.options.editForm).val(lead.city);
    $('input[name=inn]', self.options.editForm).val(lead.inn);
    $('input[name=inn_date]', self.options.editForm).val(inn_date);
    $('input[name=inn_time]', self.options.editForm).val(inn_time);
    $('textarea[name=comment]', self.options.editForm).val(lead.comment);    


    if (lead.phones.length > 0) {
      $('input[name=phone]', self.options.editForm).val(lead.phones[0].value);
      options.set('phone', lead.phones[0].value);
    } else {
      options.set('phone', '');
    }

    if (lead.emails.length > 0) {
      $('input[name=email]', self.options.editForm).val(lead.emails[0].value);
      options.set('email', lead.emails[0].value);
    } else {
      options.set('email', '');
    }


    /*if (lead.phones.length > 0) {
        for (item = 1; item <= lead.phones.length; item++) {
          $('.edit-lead-card input[name=phone' + item + ']').val(lead.phones[item - 1].value);
          console.log(lead.phones[item - 1].value);
        }
    }*/

    $('input', self.options.editForm).on('input', function() {
      options.set($(this).attr('name'), $(this).val());
      console.log(options.size);
    });

    $('textarea', self.options.editForm).on('input', function() {
      options.set($(this).attr('name'), $(this).val());
      console.log(options.size);
    });

    $('input', self.options.editForm).on('change', function() {
      options.set($(this).attr('name'), $(this).val());
      console.log(options.size);
    });

    $('.edit-lead-card .save').on('click', function(event){
      event.preventDefault();
      $.post(
        '/ajax/admin/leads.php',
        {
          action: 'edit-lead',
          lead_id: lead.id,
          created_at: options.get('lead_date') + ' ' + options.get('lead_time') + ':00',
          company_name: options.get('company_name'),
          name: options.get('name'),
          city: options.get('city'),
          inn: options.get('inn'),
          phone:  options.get('phone'),
          email: options.get('email'),
          inn_added_at: options.get('inn_date') + ' ' + options.get('inn_time') + ':00',
          comment: options.get('comment')    
        },
        function(data) {
          console.log(data);
          document.location.reload();
        },
        'json'
      );
    });

    popup.show();

    $('.close-cross').on('click', function() {
      $('.popup-root').remove();
    });
  };

  LeadsList.prototype.makeTableRow = function(lead) {
    var item;

    if (lead.phones.length != 0) {
      if (lead.phones.length == 1) {
        lead.lead_phone = lead.phones[0].value;
      } else {
        lead.lead_phone = '<ul class="lead-phones-list">';

        for (item = 0; item < lead.phones.length; item++) {
          if (item == 0) {
            lead.lead_phone = lead.lead_phone + '<li class="first-phone">' + lead.phones[item].value + '</li>'; 
          } else {
            lead.lead_phone = lead.lead_phone + '<li>' + lead.phones[item].value + '</li>';
          }
        }

        lead.lead_phone = lead.lead_phone + '</ul>';
      }
    }

    if (lead.emails.length != 0) {
      if (lead.emails.length == 1) {
        lead.lead_email = lead.emails[0].value;
      } else {
        lead.lead_email = '<ul class="lead-emails-list">';

        for (item = 0; item < lead.emails.length; item++) {
          if (item == 0) {
            lead.lead_email = lead.lead_email + '<li class="first-email">' + lead.emails[item].value + '</li>'; 
          } else {
            lead.lead_email = lead.lead_email + '<li>' + lead.emails[item].value + '</li>';
          }
        }

        lead.lead_email = lead.lead_email + '</ul>';
      }
    }

    return $('<tr>')
      .data('id', lead.id)
      .append($('<td>').html('<div class="status-color" style="background-color:' + LEAD_STATUSES[lead.status]?.color + ';"></div>' + LEAD_STATUSES[lead.status]?.name))
      .append($('<td>').text(this.formatLeadId(lead.id)))
      .append($('<td>').text(lead.inn))
      .append($('<td>').text(lead.company_name))
      .append($('<td>').html(lead.lead_phone))
      .append($('<td>').html(lead.lead_email))
      .append($('<td>').text(lead.created_at).addClass('nowrap'))
      .append($('<td>').text(lead.inn_added_at).addClass('nowrap'))
      .append($('<td>').text(lead.dealer?.name))
      .append($('<td>').text(lead.dealer?.company?.name));
  };

  LeadsList.prototype.onRowClick = function(event) {
    //const leadId = $(event.currentTarget).data('id');
    this.showLead($(event.currentTarget).data('id'));
  };

  LeadsList.prototype.showLead = function(leadId) {
    const self = this;

    $.post('/ajax/admin/leads.php', {action: 'get-lead', id: leadId}, response => {
      //console.log(response);
      const popup = new Popup({
        beforeAppend: (root, content) => {
          $(content).leadCard().leadCard('fill', response.lead);

          if (response.lead.phones.length > 0) {
            $('[data-field=phone]', content).text(response.lead.phones[0].value);
          }

          if (response.lead.emails.length > 0) {
            $('[data-field=email]', content).text(response.lead.emails[0].value);
          }

          $('.collisions tbody').children().remove();

          $('.status', content).html('<div class="status-color" style="background-color:' + LEAD_STATUSES[response.lead.status]?.color + ';"></div>' + LEAD_STATUSES[response.lead.status]?.name);
          $.post(
            '/ajax/admin/dealers.php',
            {
              action: 'get-dealer',
              id: response.lead.user_id,
            },
            function(data) {
              console.log(data);
              if (data.dealer != false) {
                $('.lead-card .dealer').text(data.dealer.name);
                $('.lead-card .dealer-company').text(data.dealer.company.name);
                $('.lead-card .dealer-filter-name').attr('href', $('.lead-card .dealer-filter-name').attr('href') + '?filter_name=' + data.dealer.name);
                $('.lead-card .dealer-filter-company').attr('href', $('.lead-card .dealer-filter-company').attr('href') + '?filter_company=' + data.dealer.company.id);
              }
            },
            'json'
          );

          $('.datetime', content).text(response.lead.created_at + ' мск');

           $.post(
              '/ajax/admin/leads.php',
              {action: 'get-intersections-lead', lead_id: leadId},
              function(response) {
                $('.collisions tbody').children().remove();
                console.log(response);
                if (response.length > 0) {
                  var item;
                  for (item = 0; item < response.length; item++) {
                    if (item == 0) {
                      $('.collisions tbody').append($('<tr>').html('<td>Пересечения</td><td class="inserection-lead" data-id="' + response[item].id + '" data-type="' + response[item].type + '">' + self.formatLeadId(response[item].recurring_lead) + '</td><td>' + response[item].fields + '</td>'));
                    } else {
                      $('.collisions tbody').append($('<tr>').html('<td></td><td class="inserection-lead" data-id="' + response[item].id + '" data-type="' + response[item].type + '">' + self.formatLeadId(response[item].recurring_lead) + '</td><td>' + response[item].fields + '</td>'));
                    }
                  }
                }
                
                $('.inserection-lead').on('click', function() {
                  var card_type = $(this).data('type');
                  $.post(
                    '/ajax/front/leads.php',
                    {action: 'get-notification-by-id', id: $(this).data('id')},
                    function(response) {
                      popup.close();

                      if (card_type == 'reference') {
                        const popupInserection = new Popup({
                          title: 'Пересечение',
                          content: $('.reference-card')
                        });

                        $.post(
                          '/ajax/front/leads.php',
                          {action: 'get-lead', id: response.recurring_lead},
                          function(recurring_lead) {
                            recurring_lead = recurring_lead.lead;
                            $('.lead-id', '.reference-card').text(self.formatLeadId(response.lead));
                            $('.notification-descr', '.reference-card').text(self.formatLeadId(recurring_lead.id) + ' от ' + recurring_lead.created_at + ' ' + recurring_lead.name + ' ' + recurring_lead.phone);
                            $('.intersection-field', '.reference-card').text(response.fields);
                          },
                          'json'
                        );

                        popupInserection.show();
                      }

                      if (card_type == 'coincidence') {
                        const popupInserection = new Popup({
                          title: 'Пересечение',
                          content: $('.coincidence-card')
                        });

                        $.post(
                          '/ajax/front/leads.php',
                          {action: 'get-lead', id: response.recurring_lead},
                          function(recurring_lead) {
                            recurring_lead = recurring_lead.lead;
                            $('.lead-id', '.coincidence-card').text(self.formatLeadId(response.lead));
                            $('.notification-descr', '.coincidence-card').text(self.formatLeadId(recurring_lead.id) + ' от ' + recurring_lead.created_at + ' ' + recurring_lead.name + ' ' + recurring_lead.phone);
                            $('.intersection-field', '.coincidence-card').text(response.fields);
                          },
                          'json'
                        );

                        popupInserection.show();
                      }

                    },
                    'json'
                  );                 
                });
              },
              'json'
            );

          $('.lead-card textarea[name=comment]').attr('disabled', 'disabled');
          content.on('delete.dealer', event => {
            popup.close();
            self.deleteLead(response.lead)
          });
          content.on('edit.dealer', event => {
            popup.close();
            self.editLead(response.lead);
          });
          content.on('done_lead.dealer', event => {
            popup.close();
            self.doneLead(response.lead);
          });
        },
        content: self.options.leadCard
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
    
    $('.custom-select').customSelect();

    const leadsList = new LeadsList({
      addForm: $('.add-lead-form'),
      editForm: $('.edit-lead-form'),
      addTitle: 'Новый лид',
      leadCard: $('.lead-card'),
      filter: $('.data-table-filter'),
      deleteLead: $('.delete-lead-open'),
      doneLead: $('.done-lead-open'),
      limit: 100,
      page: 1,
      pagerContainer: $('.pager'),
      recordsContainer: $('.data-table')
    });
  });
})(jQuery)