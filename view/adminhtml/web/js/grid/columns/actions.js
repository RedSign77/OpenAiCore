define([
    'Magento_Ui/js/grid/columns/actions',
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function (Actions, $, modal, $t) {
    'use strict';

    return Actions.extend({

        /**
         * Called by the callback defined in Actions.php prepareDataSource.
         */
        viewRecord: function (params) {
            var items = this.source && this.source.get('data.items') || [];
            var item  = items.find(function (i) { return +i.log_id === +params.id; });
            var href  = item && item.actions && item.actions.view && item.actions.view.href;

            if (href) {
                this._openSlideModal(href);
            }
        },

        /**
         * Override default action handler — open modal for "view".
         */
        applyAction: function (actionName, rowIndex) {
            if (actionName === 'view') {
                var action = this.getAction(rowIndex, actionName);

                if (action && action.href) {
                    this._openSlideModal(action.href);
                }
                return;
            }
            return this._super(actionName, rowIndex);
        },

        _openSlideModal: function (url) {
            var modalId = 'aicore-log-modal-container';
            var $wrap   = $('#' + modalId);

            if (!$wrap.length) {
                $wrap = $('<div>', { id: modalId }).appendTo('body');
            }

            $wrap.html(
                '<div style="padding:40px;text-align:center;color:#666">' + $t('Loading…') + '</div>'
            );

            var options = {
                type: 'slide',
                title: $t('AI Log Details'),
                modalClass: 'aicore-log-modal',
                responsive: true,
                innerScroll: true,
                buttons: [{
                    text: $t('Close'),
                    class: 'action-secondary action-dismiss',
                    click: function () {
                        this.closeModal();
                    }
                }]
            };

            modal(options, $wrap);
            $wrap.modal('openModal');

            $.ajax({
                url: url,
                type: 'GET',
                cache: false,
                success: function (html) {
                    $wrap.html(html);
                },
                error: function () {
                    $wrap.html(
                        '<p style="padding:20px;color:#d32f2f">' + $t('Failed to load record.') + '</p>'
                    );
                }
            });
        }
    });
});
