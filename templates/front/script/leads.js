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
      .append($('<td>').text(lead.created_at))
      .append($('<td>').text(lead.inn_added_at));
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
        '/ajax/front/leads.php',
        {
          action: 'delete-lead',
          id: lead.id
        },
        function() {
          $.post(
            '/ajax/front/notification.php',
            {
              action: 'delete_notification',
              options: {
                0: ['lead', lead.id]
              }
            },
            function(response) {
              document.location.reload();
            },
            'json'
          );
        },
        'json'
      );
      $('.popup-root').remove();
    });
  };

  


  LeadsList.prototype.load = function(callback) {
    $.post(
      '/ajax/front/leads.php',
      {
        action: 'list-leads',
        limit: this.options.limit,
        page: this.options.page,

        filter_dealer_id: $('input[name=dealer_id]', this.options.recordsContainer).val(),
        filter_status: $('.filter-status', this.options.recordsContainer).data('val'),
        filter_id: $('input[name=id]', this.options.recordsContainer).val(),
        filter_inn: $('input[name=inn]', this.options.recordsContainer).val(),
        filter_company_name: $('input[name=company_name]', this.options.recordsContainer).val(),
        filter_phone: $('input[name=phone]', this.options.recordsContainer).val(),
        filter_email: $('input[name=email]', this.options.recordsContainer).val(),
        filter_inn_sort: $('.inn-sort').data('sort'),
        filter_date_sort: $('.date-sort').data('sort'),
        filter_leads_from: $('input[name=leads_from]').val(),
        filter_leads_to: $('input[name=leads_to]').val(),
      },
      function(response) {
        console.log(response);
        if ('errors' in response && response.errors.length) {
          alert(response.errors.map(error => error.error).jsoin("\n"));
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

  LeadsList.prototype.onAddFormSubmit = function(callback) {
    const self = this;

  //console.log($(event.currentTarget).serialize());

    
    $.post(
      '/ajax/front/leads.php',
      $(event.currentTarget).serialize(),
      function(data) {
        console.log(data);
        if (data) {
          if ('errors' in data && data.errors.length) {
            DisplayFormErrors(self.options.form, data.errors);
            return;
          }
        } else {
          alert('Сервер не ответил');
        }
      },
      'json'
    );

    document.location.reload();
  };

  LeadsList.prototype.onRowClick = function(event, lead_id = '') {
    const self = this;

    if (lead_id == '') {
      const row = $(event.currentTarget);
      var leadId = row.data('id');
    } else {
      var leadId = lead_id;
    }
    
    var lead_comment = '';
    $('.collisions tbody').children().remove();

    $.post(
      '/ajax/front/leads.php',
      {action: 'get-lead', id: leadId},
      function(response) {
        if ('errors' in response && response.errors.length) {
          alert(response.errors.map(error => error.error)).join("\n");
          return;
        }

        if ('lead' in response) {
          if (response.lead) {

            $.post(
              '/ajax/front/leads.php',
              {action: 'get-intersections-lead', lead_id: leadId},
              function(response) {
                $('.collisions tbody').children().remove();
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

            $('.done-lead').css('display', 'none');
            $('.lead-card-save_btn').css('background', '#CBCBCB');
            $('.edit').data('lead', leadId);
            $('.id').text(self.formatLeadId(leadId));
            $('.status', self.options.leadCard).html('<div class="status-color" style="background-color:' + LEAD_STATUSES[response.lead.status]?.color + ';"></div>' + LEAD_STATUSES[response.lead.status]?.name);
            $('.datetime', self.options.leadCard).text(response.lead.created_at);
            $('td[data-field="inn"]', self.options.leadCard).text(response.lead.inn);
            $('td[data-field="inn_added_at"]', self.options.leadCard).text(response.lead.inn_added_at);
            $('td[data-field="company_name"]', self.options.leadCard).text(response.lead.company_name);
            $('td[data-field="city"]', self.options.leadCard).text(response.lead.city);
            $('td[data-field="name"]', self.options.leadCard).text(response.lead.name);
            $('textarea[data-field="comment"]', self.options.leadCard).val(response.lead.comment);

            if (response.lead.phones.length != 0) {
              $('td[data-field="phone"]', self.options.leadCard).text(response.lead.phones[0].value);
            } else {
              $('td[data-field="phone"]', self.options.leadCard).text('');
            }
            
            if (response.lead.emails.length != 0) {
              $('td[data-field="email"]', self.options.leadCard).text(response.lead.emails[0].value);
            } else {
              $('td[data-field="email"]', self.options.leadCard).text('');
            }


            const popup = new Popup({
              content: self.options.leadCard
            });
            popup.show();

            $('.edit').click(function() {
              popup.close();
              const popupEditLead = new Popup({
                content: self.options.editLeadForm
              });

              $('.popup-root').remove();
              popupEditLead.show();

              self.editLead(response.lead.id);
              
              $('input[name=phone1]').mask(
                '+7 (999) 999-99-99',
                { autoclear: false }
              );
              $('input[name=phone2]').mask(
                '+7 (999) 999-99-99',
                { autoclear: false }
              );
              $('input[name=phone3]').mask(
                '+7 (999) 999-99-99',
                { autoclear: false }
              );
            });    

            $('.delete').on('click', function() {
              popup.close();
              self.deleteLead(response.lead);
            });

            $('[data-field="comment"]').on('input', function() {
              lead_comment = $(this).val();
              $('.lead-card-save_btn').css('background', '#E86C42');
            });

            $('.lead-card-save_btn').on('click', function() {
              $.post(
                '/ajax/front/leads.php',
                {
                  action: 'update-lead-comment',
                  id: leadId,
                  comment: lead_comment,
                },

                function(response) {},
                'json'
              );

              $('.lead-card-save_btn').css('background', '#CBCBCB');
            });

            $('.close-cross').on('click', function() {
              $('.popup-root').remove();
            });
          } else {
            alert('Лид не найден');
          }
          return;
        }

        alert('Данные лида не получены');
      },
      'json'
    );
  };

  LeadsList.prototype.editLead = function (lead_id) {
    const self = this;
    var lead;

    $.ajax({
      url: '/ajax/front/leads.php',
      method: 'post',
      dataType: 'json',
      data: {action: 'get-lead', id: lead_id},
      success: function(response) {
        lead = response.lead;
      },
      async: false
    });

    var options = new Map ([
      ['name', lead.name],
      ['inn', lead.inn],
      ['company_name', lead.company_name],
      ['phone1', ''],
      ['phone2', ''],
      ['phone3', ''],
      ['email1', ''],
      ['email2', ''],
      ['email3', ''],
      ['city', lead.city],
      ['comment', lead.comment]
    ]);


    $('[data-field="id"]', '.edit-lead-form').text(formatLeadId(lead.id));
    $('.status', '.edit-lead-form').html('<div class="status-color" style="background-color:' + LEAD_STATUSES[lead.status]?.color + ';"></div>' + LEAD_STATUSES[lead.status]?.name);
    $('.datetime', '.edit-lead-form').text(lead.created_at);
    
    $('[name="city"]', '.edit-lead-form').val(lead.city);
    $('[name="name"]', '.edit-lead-form').val(lead.name);
    $('[name="comment"]', '.edit-lead-form').val(lead.comment);


    if (lead.inn_added_at == null) {
      $('.company-name-field').html('<input type="text" name="company_name" value="" disabled/>');
      $('.inn-field', ).html('<input type="text" name="inn" value="" /><button class="search-company-btn"></button>');
      $('[name="company_name"]', '.edit-lead-form').val('');
    } else {
      $('.inn-field', ).html(lead.inn);
      $('.company-name-field', '.edit-lead-form').html(lead.company_name);
    }

    for (var item = 0; item < lead.phones.length; item++){
      $('[name="phone' + (item + 1) + '"]', '.edit-lead-form').val(lead.phones[item].value);
      options.set('phone' + (item + 1), lead.phones[item].value);
    }

    for (var item = 0; item < lead.emails.length; item++){
      $('[name="email' + (item + 1) + '"]', '.edit-lead-form').val(lead.emails[item].value);
      options.set('email' + (item + 1), lead.emails[item].value);
    }

    $('.open-field-text').on('click', function() {
      var type = $(this).data('type');
      var field = $(this).data('form-field');
      var item = $(this).data('open-field-item');

      if (type == 'open') {
        $('[data-action-form-field="' + field + '"]').css('display', 'table-row');
        $('[data-form-field="' + item  + '"]').css('visibility', 'hidden');
        $(this).text('-');
        $(this).data('type', 'close');
      } else {
        $('[data-action-form-field="' + field + '"]').css('display', 'none');
        $('[data-form-field="' + item  + '"]').css('visibility', 'visible');
        $(this).text('+');
        $(this).data('type', 'open');
      }
    });

    if (lead.phones.length > 0) {
        for (item = 1; item <= lead.phones.length; item++) {
          $('.edit-lead-card input[name=phone' + item + ']').val(lead.phones[item - 1].value);
        }
    }

    if (lead.emails.length > 0) {
        for (item = 1; item <= lead.emails.length; item++) {
          $('.edit-lead-card input[name=email' + item + ']').val(lead.emails[item - 1].value);
        }
    }

    $('input', '.edit-lead-form').on('input', function() {
      options.set($(this).attr('name'), $(this).val());
    });

    $('textarea', '.edit-lead-form').on('input', function() {
      options.set($(this).attr('name'), $(this).val());
    });

    $('input', '.edit-lead-form').on('change', function() {
      options.set($(this).attr('name'), $(this).val());
    });

    $('.edit-lead-form input[name=inn]').on('input', function() {
      inn = $(this).val();
    });

    $('.edit-lead-form .search-company-btn').on('click', function(event) {
      event.preventDefault();
      var url = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/party";
      var token = "04e24a48faf5e9dc41b1179f6e22b332e4cccbaa";
      var query = inn;
  
      var options = {
          method: "POST",
          mode: "cors",
          headers: {
              "Content-Type": "application/json",
              "Accept": "application/json",
              "Authorization": "Token " + token
          },
          body: JSON.stringify({query: query})
      }
      
      fetch(url, options)
      .then(response => response.json())
      .then(result => $('.edit-lead-form input[name=company_name]').val(result.suggestions[0].value))
      .catch(error => $('.edit-lead-form input[name=company_name]').val(''));
    });

    $('.save-edit-lead').on('click', function(e) {
      e.preventDefault();
      if (options.get('company_name') == null || options.get('company_name') == '') {
        options.set('company_name', $('.edit-lead-form input[name=company_name]').val());
      }
      
      $.post(
        '/ajax/front/leads.php',
        {
          action: 'edit-lead',
          lead_id: lead.id,
          name: options.get('name'),
          inn: options.get('inn'),
          company_name: options.get('company_name'),
          city: options.get('city'),
          phone1: options.get('phone1'),
          phone2: options.get('phone2'),
          phone3: options.get('phone3'),
          email1: options.get('email1'),
          email2: options.get('email2'),
          email3: options.get('email3'),
          comment: options.get('comment')
        },
        function(data) {
          console.log(data);
          if ('errors' in data && data.errors.length) {
            alert(data.errors.map(error => error.error)).join("\n");
            return;
          }

          $('.popup-root').remove();
          document.location.reload();
        },
        'json'
      );
    });

    $('.close-cross').on('click', function() {
      $('.popup-root').remove();
    });
  };

  /*$(document).ready(function() {
    new LeadsList({
      addButton: $('#add-lead'),
      addForm: $('.add-lead-form'),
      addTitle: 'Новый лид',
      leadCard: $('.lead-card'),
      page: 1,
      limit: 20,
      pagerContainer: $('#leads-list-pager'),
      recordsContainer: $('#leads-list')
    });
  });*/

  $(document).ready(function() {
    var inn;
    var item;
    console.log(LEAD_STATUSES);
    $('.custom-select').customSelect();

    function check_organization(organization) {
      var organization_string = organization;
      organization = organization.split(' '); 

      if ((organization[0].toLowerCase() == 'общество') && (organization[3].toLowerCase() == 'ответственностью')) {
        organization_string = 'ООО ';

        for (item = 4; item < organization.length; item++) {
          organization_string = organization_string + organization[item] + ' ';
        }
      } 

      if ((organization[0].toLowerCase() == 'индивидуальный') && (organization[1].toLowerCase() == 'предприниматель')) {
        organization_string = 'ИП ';

        for (item = 2; item < organization.length; item++) {
          organization_string = organization_string + organization[item] + ' ';
        }
      }

      return organization_string;
    } 

    const leadsList = new LeadsList({
      addButton: $('#add-lead'),
      addForm: $('.add-lead-form'),
      addTitle: 'Новый лид',
      leadCard: $('.lead-card'),
      deleteLead: $('.delete-lead-open'),
      editLeadForm: $('.edit-lead-form'),
      filter: $('.data-table-filter'),
      limit: 100,
      page: 1,
      pagerContainer: $('.pager'),
      recordsContainer: $('.data-table')
    });

    $('.add-lead-form input[name=inn]').on('input', function() {
      inn = $(this).val();
    });

    $('.add-lead-form .search-company-btn').click(function(event) {
      event.preventDefault();
      var url = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/party";
      var token = "04e24a48faf5e9dc41b1179f6e22b332e4cccbaa";
      var query = inn;
  
      var options = {
          method: "POST",
          mode: "cors",
          headers: {
              "Content-Type": "application/json",
              "Accept": "application/json",
              "Authorization": "Token " + token
          },
          body: JSON.stringify({query: query})
      }
      
      fetch(url, options)
      .then(response => response.json())
      .then(result => $('.add-lead-form input[name=company_name]').val(check_organization(result.suggestions[0].value)))
      .catch(error => $('.add-lead-form input[name=company_name]').val(''));
    });
  });

  $('.close-cross').on('click', function() {
    $('.popup-root').remove();
  });



  function formatLeadId (id) {
    return '#' + ('0'.repeat(6 - ('' + id).length)) + id;
  };

  function editLead(argument) {
    console.log($('.edit').data('lead'));
    var lead; 

    $('.popup-root').remove();

    $.ajax({
      url: '/ajax/front/leads.php',
      method: 'post',
      dataType: 'json',
      data: {action: 'get-lead', id: $('.edit').data('lead')},
      success: function(response) {
        lead = response.lead;
      },
      async: false
    });

    const self = this;

    var options = new Map ([
      ['name', lead.name],
      ['inn', lead.inn],
      ['company_name', lead.company_name],
      ['phone1', ''],
      ['phone2', ''],
      ['phone3', ''],
      ['email1', ''],
      ['email2', ''],
      ['email3', ''],
      ['city', lead.city],
      ['comment', lead.comment]
    ]);


    $('[data-field="id"]', '.edit-lead-form').text(formatLeadId(lead.id));
    $('.status', '.edit-lead-form').html('<div class="status-color" style="background-color:' + LEAD_STATUSES[lead.status]?.color + ';"></div>' + LEAD_STATUSES[lead.status]?.name);
    $('.datetime', '.edit-lead-form').text(lead.created_at);
    
    $('[name="city"]', '.edit-lead-form').val(lead.city);
    $('[name="name"]', '.edit-lead-form').val(lead.name);
    $('[name="comment"]', '.edit-lead-form').val(lead.comment);


    if (lead.inn_added_at == null) {
      $('.company-name-field').html('<input type="text" name="company_name" value="" disabled/>');
      $('.inn-field', ).html('<input type="text" name="inn" value="" /><button class="search-company-btn"></button>');
      $('[name="company_name"]', '.edit-lead-form').val('');
    } else {
      $('.inn-field', ).html(lead.inn);
      $('.company-name-field', '.edit-lead-form').html(lead.company_name);
    }

    for (var item = 0; item < lead.phones.length; item++){
      $('[name="phone' + (item + 1) + '"]', '.edit-lead-form').val(lead.phones[item].value);
      options.set('phone' + (item + 1), lead.phones[item].value);
    }

    for (var item = 0; item < lead.emails.length; item++){
      $('[name="email' + (item + 1) + '"]', '.edit-lead-form').val(lead.emails[item].value);
      options.set('email' + (item + 1), lead.emails[item].value);
    }

    $('.open-field-text').on('click', function() {
      var type = $(this).data('type');
      var field = $(this).data('form-field');
      var item = $(this).data('open-field-item');

      if (type == 'open') {
        $('[data-action-form-field="' + field + '"]').css('display', 'table-row');
        $('[data-form-field="' + item  + '"]').css('visibility', 'hidden');
        $(this).text('-');
        $(this).data('type', 'close');
      } else {
        $('[data-action-form-field="' + field + '"]').css('display', 'none');
        $('[data-form-field="' + item  + '"]').css('visibility', 'visible');
        $(this).text('+');
        $(this).data('type', 'open');
      }
    });

    if (lead.phones.length > 0) {
        for (item = 1; item <= lead.phones.length; item++) {
          $('.edit-lead-card input[name=phone' + item + ']').val(lead.phones[item - 1].value);
        }
    }

    if (lead.emails.length > 0) {
        for (item = 1; item <= lead.emails.length; item++) {
          $('.edit-lead-card input[name=email' + item + ']').val(lead.emails[item - 1].value);
        }
    }

    $('input', '.edit-lead-form').on('input', function() {
      options.set($(this).attr('name'), $(this).val());
    });

    $('textarea', '.edit-lead-form').on('input', function() {
      options.set($(this).attr('name'), $(this).val());
    });

    $('input', '.edit-lead-form').on('change', function() {
      options.set($(this).attr('name'), $(this).val());
    });

    $('.edit-lead-form input[name=inn]').on('input', function() {
      inn = $(this).val();
    });

    $('.edit-lead-form .search-company-btn').on('click', function(event) {
      event.preventDefault();
      var url = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/party";
      var token = "04e24a48faf5e9dc41b1179f6e22b332e4cccbaa";
      var query = inn;
  
      var options = {
          method: "POST",
          mode: "cors",
          headers: {
              "Content-Type": "application/json",
              "Accept": "application/json",
              "Authorization": "Token " + token
          },
          body: JSON.stringify({query: query})
      }
      
      fetch(url, options)
      .then(response => response.json())
      .then(result => $('.edit-lead-form input[name=company_name]').val(result.suggestions[0].value))
      .catch(error => $('.edit-lead-form input[name=company_name]').val(''));
    });

    $('.save-edit-lead').on('click', function(e) {
      e.preventDefault();
      if (options.get('company_name') == null || options.get('company_name') == '') {
        options.set('company_name', $('.edit-lead-form input[name=company_name]').val());
      }
      
      
      //console.log(options);
      $.post(
        '/ajax/front/leads.php',
        {
          action: 'edit-lead',
          lead_id: lead.id,
          name: options.get('name'),
          inn: options.get('inn'),
          company_name: options.get('company_name'),
          city: options.get('city'),
          phone1: options.get('phone1'),
          phone2: options.get('phone2'),
          phone3: options.get('phone3'),
          email1: options.get('email1'),
          email2: options.get('email2'),
          email3: options.get('email3'),
          comment: options.get('comment')
        },
        function(data) {
          console.log(data);
          /*if ('errors' in data && data.errors.length) {
            alert(data.errors.map(error => error.error)).join("\n");
            return;
          }*/

          //$('.popup-root').remove();
          //document.location.reload();
           //self.onRowClick(e, lead.id);
        },
        'json'
      );
    });

    $('.close-cross').on('click', function() {
      $('.popup-root').remove();
    });
  }

  $.post(
    '/ajax/front/leads.php',
    {
      action: 'check-intersections-lead',
    },
    function(response) {
        console.log(response);
    },
    'json'
  );
})(jQuery)