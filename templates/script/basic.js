// Entity type utils

UTIL = {
  datetime: {
    formatDate: date => {
      return [
        ('' + date.getDate()).padStart(2, '0'),
        ('' + (date.getMonth() + 1)).padStart(2, '0'),
        '' + date.getFullYear()
      ].join('.');
    },

    formatTime: date => {
      return [
        ('' + date.getHours()).padStart(2, '0'),
        ('' + date.getMinutes()).padStart(2, '0')
      ].join(':');
    }
  },

  lead: {
    formatId: id => {
      return '#' + '0'.repeat(6 - ('' + id).length) + id;
    }
  }
};

// Misc

jQuery.fn.fillFields = function(entity) {
  const getValues = (obj, prefix) => {
    let values = {};

    for (const field in obj) {
      const value = obj[field];

      if (typeof value === 'object') {
        Object.assign(values, getValues(value, `${field}-`));
      } else {
        values[prefix + field] = value;
      }
    }

    return values;
  };

  this.each((_, container) => {
    const $container = $(container);
    const values = getValues(entity, '');

    for (const field in values) {
      const places = $container.find(`[data-field='${field}']`);

      places.each((_, place) => {
        const $place = $(place);
        $place.text(values[field]);
      });
    }
  });
};

// Form

function DisplayFormErrors(form, errors) {
  for (const error of errors) {
    $(`[name=${error.field}]`, form).addClass('error');
  }
};

function ShowErrors(errors, form) {
  let errorMessages = [];

  if (form) {
    for (const error of errors) {
      if ('field' in error) {
        $(`input[name=${error.field}]`, form).addClass('error');
      } else {
        errorMessages.push(error);
      }
    }
  }

  const errorMessage = errorMessages.map(error => error.error).join("\n");
}


// Popup

function Popup(options) {
  this.options = options;
  this.filterTimeout = false;
}

Popup.prototype.close = function() {
  this.root.remove();

  if ('afterClose' in this.options && this.options.afterClose) {
    this.options.afterClose();
  }
};

Popup.prototype.show = function() {
  const content = $(this.options.content).clone(true);
  content.data('popup', this);
  content.wrap($('<div class="popup-root"><div class="popup-content">'));
  const root = content.closest('.popup-root');


  if ('title' in this.options && this.options.title) {
    $('.popup-content', root).prepend($('<div class="popup-title">').text(this.options.title));
  }

  $('.popup-content', root).prepend('<span class="close-cross">✕</span>');

  root.on('click', event => {
    if (event.target === event.currentTarget) {
      this.close();
    }
  });

  if ('beforeAppend' in this.options && this.options.beforeAppend) {
    this.options.beforeAppend(root, content);
  }

  this.root = root;

  $('body').append(root);

  $('.close-cross').on('click', function() {
    $('.popup-root').remove();
  });

}

// Pseudo-select

function CustomSelect(el, options = {}) {
  this.el = el;
  this.options = options;
  this.caption = $('.caption', el).text();

  this.init();
}

CustomSelect.prototype.init = function() {
  const self = this;

  this.el.on('click', '.list li', function(event) {
    const option = $(event.currentTarget);
    const id = option.data('id');

    self.el.data('val', id || null);
    $('.caption', self.el).text(id ? option.text() : self.caption);

    self.el.trigger('change');
  });
};

jQuery.fn.customSelect = function(options = {}) {
  this.each((i, el) => {
    el = $(el);

    if (!el.data('customSelect')) {
      el.data('customSelect', new CustomSelect(el, options));
    }
  });
};

// DataTable

var DataTable = {
  init() {
    const self = this;

    if ('onRowClick' in self) {
      self.options.recordsContainer.on('click', 'tr', $.proxy(self.onRowClick, self));
    }

    // Filter
    $('.filter-select', self.options.filter).customSelect();
    $('input[type=text]', self.options.filter).on('input', $.proxy(this.onFilterChanged, this));
    $('input[type=date]', self.options.filter).on('change', $.proxy(this.onFilterChanged, this));
    $('.filter-sort', self.options.filter).on('click', $.proxy(this.onFilterChanged, this));
    $('.filter-select', self.options.filter).on('change', $.proxy(this.onFilterChanged, this));

    // Add form
    if (self.options.addButton) {
      self.options.addButton.on('click', function(event) {
        $('input[name=id]', self.options.addForm).val('');

        const popup = new Popup({
          beforeAppend: function(popup, form) {
            self.options.form = form;
            $('input[name=phone]', popup).mask(
              '+7 (999) 999-99-99',
              { autoclear: false }
            );
            $('input[name=phone1]', popup).mask(
              '+7 (999) 999-99-99',
              { autoclear: false }
            );
            $('input[name=phone2]', popup).mask(
              '+7 (999) 999-99-99',
              { autoclear: false }
            );
            $('input[name=phone3]', popup).mask(
              '+7 (999) 999-99-99',
              { autoclear: false }
            );
          },
          content: self.options.addForm,
          title: self.options.addTitle
        });
        popup.show();

        $('.open-field').on('click', function() {
          var type = $(this).data('type');
          var field = $(this).data('field');
          var item = $(this).data('open-field-item');

          if (type == 'open') {
            $('[data-action-field="' + field + '"]').css('display', 'table-row');
            $('[data-field="' + item  + '"]').css('visibility', 'hidden');
            $(this).text('-');
            $(this).data('type', 'close');
          } else {
            $('[data-action-field="' + field + '"]').css('display', 'none');
            $('[data-field="' + item  + '"]').css('visibility', 'visible');
            $(this).text('+');
            $(this).data('type', 'open');
          }
        });

        $('.close-cross').on('click', function() {
          $('.popup-root').remove();
        });
      });
    }

    if (self.options.addForm) {
      self.options.addForm.on('submit', function(event) {
        event.preventDefault();

        const form = $(event.currentTarget);
        $('input', form).removeClass('error');

        self.onAddFormSubmit(event);
      });
    }

    // Pager
    $('.custom-select', self.options.pagerContainer).customSelect();
    $('.pages', self.options.pagerContainer).on('click', 'li', function(event) {
      self.changePage($(event.target).data('page'));
    });
    $('.custom-select', self.options.pagerContainer).on('change', function(event) {
      console.log(event);
      self.options.limit = $(event.currentTarget).data('val');
      console.log('...', self.options.limit);
      self.options.page = 1;
      self.onFilterChanged();
    });

    // Done
    this.refresh();
  },

  append(records) {
    $('tbody', this.options.recordsContainer)
      .append(...records.map($.proxy(this.makeTableRow, this)));
  },

  changePage(page) {
    this.options.page = page;
    this.refresh();
  },

  drawPager(pager) {
    const pagesContainer = $('.pages', this.options.pagerContainer);
    pagesContainer.empty();

    if (pager.pages > 1) {
      for (let page = 1; page <= pager.pages; page++) {
        pagesContainer.append(
          $('<li>')
            .addClass(page === this.options.page ? 'current' : null)
            .text(page)
            .data('page', page)
        );
      }
    }

    $('.hint', this.options.pagerContainer).html(`
      ${this.options.limit * (this.options.page - 1) + 1}
      &mdash;
      ${this.options.limit * this.options.page}
      из ${pager.total}
    `);
  },

  onFilterChanged() {
    const self = this;

    if (self.filterTimeout) {
      clearTimeout(self.filterTimeout);
      self.filterTimeout = null;
    }

    self.filterTimeout = setTimeout(function() {
      self.refresh();
      self.filterTimeout = false;
    }, 1000);
  },

  truncate() {
    $('tbody', this.options.recordsContainer).empty();
  },

  refresh() {
    const self = this;

    this.load(function(data) {
      self.truncate();
      self.append(data.records);
      self.drawPager(data.pager);
    });
  }
};

