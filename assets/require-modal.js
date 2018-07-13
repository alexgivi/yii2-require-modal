RequireModal = {

    loaderImageUrl: null,

    requireButtons: [],
    modal: null,
    form: null,

    currentRequireItems: [],
    currentModalTitle: null,
    currentModalParams: null,
    currentButtonId: null,

    init: function () {
        this.initCommonComponents();
        this.requireButtons = $('[data-require]');
        this.modal = $('#require-modal');
        $('body').append(modal);
        this.form = this.modal.find('form');
        this.bindFormSubmit();
        this.bindRequireClick();
    },

    initCommonComponents: function () {
        this.initDatePickers();
    },

    initDatePickers: function () {
        $('.datepicker').datepicker({dateFormat: 'dd.mm.yy'});
        $('.timepicker').timepicker({timeFormat: 'HH:mm'});
        $('.datetimepicker').datetimepicker({
            dateFormat: 'dd.mm.yy',
            timeFormat: 'HH:mm'
        });
    },

    bindRequireClick: function () {
        var self = this;
        this.requireButtons.off('click').on('click', function (event) {
            event.stopPropagation();

            self.currentRequireItems = $(this).data('require');
            self.currentModalTitle = $(this).data('modal-title');
            self.currentButtonId = $(this).attr('id');

            var method = $(this).data('method') || 'post';
            self.form.attr('action', $(this).attr('href'));
            self.form.attr('target', $(this).attr('target'));
            self.form.attr('method', method);

            self.bindModal();
            self.modal.modal('show');

            return false;
        });

    },

    validateRequired: function () {
        var inputs = this.form.find('.form-control[required]');
        for (var i = 0; i < inputs.length; ++i) {
            var input = $(inputs[i]);
            var errorBlock = input.closest('.form-group').find('.error-place');
            if (input.val().trim() === '') {
                errorBlock.html('Поле необходимо заполнить.');
                return false;
            } else {
                errorBlock.html('');
            }
        }

        return true;
    },

    toTimestamp: function (date) {
        var myDate = date.split(".");
        var newDate = myDate[1] + "/" + myDate[0] + "/" + myDate[2];
        return new Date(newDate).getTime();
    },

    validateMin: function () {
        var inputs = this.form.find('.form-control[min]');
        for (var i = 0; i < inputs.length; ++i) {
            var input = $(inputs[i]);
            var errorBlock = input.closest('.form-group').find('.error-place');
            var value = input.val();
            var min = input.attr('min');
            if (input.hasClass('datepicker') || input.hasClass('timepicker') || input.hasClass('datetimepicker')) {
                value = this.toTimestamp(value);
                min = this.toTimestamp(min);
            } else {
                value = parseInt(value);
                min = parseInt(min);
            }

            if (value < min) {
                errorBlock.html('Минимальное значение: ' + input.attr('min'));
                return false;
            } else {
                errorBlock.html('');
            }
        }

        return true;
    },

    validateForm: function () {
        if (!this.validateRequired()) {
            return false;
        }

        return this.validateMin();
    },

    bindFormSubmit: function () {
        var self = this;
        this.form.off('submit').on('submit', function () {
            if (!self.validateForm()) {
                return false;
            }

            // todo проверка таргета, тут подразумевается что таргет - бланк
            if ($(this).attr('target')) {
                return true;
            }

            var submitButton = $('#require-modal-submit');
            submitButton.attr('disabled', true);
            if (self.loaderImageUrl) {
                submitButton.html('<img src="' + self.loaderImageUrl + '">');
            }
        });
    },

    generateModalBody: function () {
        var html = '';
        for (var i = 0; i < this.currentRequireItems.length; i++) {
            html += this.generateFieldHtml(this.currentRequireItems[i]);
        }
        return html;
    },

    inputHtml: function (id, name, value, type, options) {
        var _class = 'form-control';
        if (type === 'date') {
            type = 'text';
            _class += ' datepicker';
        } else if (type === 'time') {
            type = 'text';
            _class += ' timepicker';
        } else if (type === 'datetime') {
            type = 'text';
            _class += ' datetimepicker';
        }

        var html = '<input ' +
            'id="' + id + '" ' +
            'class="' + _class + '" ' +
            'type="' + type + '" ' +
            'name="' + name + '" ' +
            'value="' + value + '" ';

        for (var option in options) {
            if (!options.hasOwnProperty(option)) {
                continue;
            }

            var opValue = options[option];

            if (option === 'required') {
                opValue = 'required';
            }
            if (option === 'multiple') {
                opValue = 'multiple';
            }

            html += ' ' + option + '="' + opValue + '"';
        }

        html += '>';
        return html;
    },

    textAreaHtml: function (id, name, value, options) {
        var html = '<textarea ' +
            'id="' + id + '" ' +
            'class="form-control" ' +
            'name="' + name + '" ';

        if (options.required) {
            html += ' required="required"';
        }
        if (options.minlength) {
            html += ' minlength="' + options['minlength'] + '"';
        }
        html += '>' + value + '</textarea>';
        return html;
    },

    dropDownListHtml: function (id, name, value, options) {
        var html = '<select ';
        if (options.multiple) {
            html += ' multiple="multiple" ';
        }
        if (options.size) {
            html += ' size="' + options.size + '" ';
        }
        html += ' id="' + id + '" class="form-control"';

        if (options.required) {
            html += ' required="required"';
        }

        html += ' name="' + name + '">';

        for (var key in options.items) {
            html += '<option value="' + key + '"';
            if (key === value) {
                html += ' selected="selected"';
            }
            html += '>' + options.items[key] + '</option>';
        }
        html += '</select>';

        return html;
    },

    checkBoxListHtml: function (id, name, value, options) {
        var html = '';
        if (options.inline) {
            html = '<br><div class="checkbox checkbox-inline hidden"></div>'
        }

        for (var key in options.items) {
            if (!options.items.hasOwnProperty(key)) {
                continue;
            }

            html += '<div class="checkbox' +
                (options.inline ? ' checkbox-inline" style="padding-left: 0;">' : '">') +
                '<label><input id="' + id + '" type="checkbox" name="' +
                name + '" value="' + key + '"';
            if (value.indexOf(key) !== -1 || value.indexOf(parseInt(key)) !== -1) {
                html += ' checked';
            }
            html += '>' + options.items[key] + '</label></div> ';
        }

        return html;
    },

    radioListHtml: function (id, name, value, options) {
        var html = '';
        if (options.inline) {
            html = '<br><div class="radio radio-inline hidden"></div>'
        }

        for (var key in options.items) {
            html += '<div class="radio' +
                (options.inline ? ' radio-inline" style="padding-left: 0;">' : '">') +
                '<label><input id="' + id + '" type="radio" name="' +
                name + '" value="' + key + '"';
            if (value === key) {
                html += ' checked';
            }
            html += '>' + options.items[key] + '</label></div> ';
        }

        return html;
    },

    checkBoxHtml: function (id, label, name, value, options) {
        var html = '<div class="checkbox"><label>' +
            '<input id="' + id + '" type="checkbox" name="' + name + '"';
        if (value) {
            html += 'value="' + value + '"';
        }
        if (options.checked) {
            html += ' checked';
        }
        html += '>' + label + '</label></div> ';
        return html;
    },

    generateFieldHtml: function (item) {
        var type = item.type || 'text';

        var name = item.name || '';
        var value = item.value || '';
        var id = this.currentButtonId + '-' + name;
        var options = item['options'] || [];

        if (type === 'div') {
            return '<div>' + item.label + '</div>';
        } else if (type === 'header') {
            return '<hr><h4 class="text-center">' + item.label + '</h4>';
        } else if (type === 'hidden') {
            return '<input type="hidden" name="' + name + '" value="' + value + '">';
        }

        var hidden = '';
        if (item.visible !== undefined && item.visible !== true) {
            hidden = ' hidden';
        }

        var html = '<div class="form-group' + hidden + '">';
        if (type !== 'checkbox') {
            html += '<label for="' + id + '">' + item.label + '</label>';
        }

        switch (type) {
            case 'select':
                html += this.dropDownListHtml(id, name, value, options);
                break;
            case 'textarea':
                html += this.textAreaHtml(id, name, value, options);
                break;
            case 'checkBoxList':
                html += this.checkBoxListHtml(id, name, value, options);
                break;
            case 'radioList':
                html += this.radioListHtml(id, name, value, options);
                break;
            case 'checkbox':
                html += this.checkBoxHtml(id, item.label, name, value, options);
                break;
            default:
                html += this.inputHtml(id, name, value, type, options);
                break;
        }
        html += '<div class="text-danger error-place"></div>';
        html += '</div>';
        return html;
    },

    bindItemsVisibilityChange: function () {
        var self = this;
        for (var i = 0; i < this.currentRequireItems.length; i++) {
            var item = this.currentRequireItems[i];
            if (item.visible !== undefined && item.visible !== true) {
                for (var name in item.visible) {
                    this.modal.find('[name="' + name + '"]')
                        .off('change').on('change', function () {
                        self.refreshItemsVisibility();
                    });
                }
            }
        }
    },

    refreshItemsVisibility: function () {
        for (var i = 0; i < this.currentRequireItems.length; i++) {
            var item = this.currentRequireItems[i];
            if (item.visible !== undefined && item.visible !== true) {
                var control = this.modal.find('[name="' + item.name + '"]');
                var formGroup = control.parents('.form-group');

                var visible = true;
                for (var dependentName in item.visible) {
                    if (!item.visible.hasOwnProperty(dependentName)) {
                        continue;
                    }

                    var value = this.getFieldValue(dependentName);
                    var valueToCheck = item.visible[dependentName];

                    // массив значений. должно быть равно одному из них.
                    if (typeof(valueToCheck) === 'object') {
                        if (valueToCheck.indexOf(value) === -1) {
                            visible = false;
                            break;
                        }
                    } else if (valueToCheck + '' !== value + '') {
                        visible = false;
                        break;
                    }
                }

                if (visible) {
                    if (item.options && item.options.required) {
                        control.attr('required', 'required');
                    }
                    formGroup.removeClass('hidden');
                } else {
                    if (item.options && item.options.required) {
                        control.removeAttr('required');
                    }
                    formGroup.addClass('hidden');
                }
            }
        }
    },

    getFieldValue: function (name) {
        for (var i = 0; i < this.currentRequireItems.length; i++) {
            var item = this.currentRequireItems[i];
            if (item.name === name) {
                switch (item.type) {
                    case 'radioList':
                        return this.modal.find('[name="' + name + '"]:checked').val();

                    case 'checkBoxList':
                        var value = [];
                        this.modal.find('[name="' + name + '"]:checked').each(function () {
                            value.push($(this).val());
                        });
                        return value;

                    default:
                        return this.modal.find('[name="' + name + '"]').val();
                }
            }
        }
        return undefined;
    },

    bindModal: function () {
        if (this.currentModalTitle) {
            this.modal.find('.modal-title').html(this.currentModalTitle);
        }

        this.modal.find('.modal-body').html(this.generateModalBody());
        this.initCommonComponents();
        this.bindItemsVisibilityChange();
        this.refreshItemsVisibility();
    }
};