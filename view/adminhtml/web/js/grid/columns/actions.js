define([
    'Magento_Ui/js/grid/columns/actions',
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function (Actions, $, modal, $t) {
    'use strict';

    /**
     * Shared "view in slide-over modal" row-action column.
     * Consumers configure idField / modalId / modalTitle / modalClass
     * via the UI Component XML <settings> so a single implementation
     * serves every grid that opens a record in a slide-over.
     */
    return Actions.extend({
        defaults: {
            idField: 'entity_id',
            modalId: 'aicore-view-modal-container',
            modalTitle: '',
            modalClass: 'aicore-view-modal'
        },

        /**
         * Called by the callback defined in Actions.php prepareDataSource.
         */
        viewRecord: function (params) {
            var items = this.source && this.source.get('data.items') || [];
            var idField = this.idField;
            var item = items.find(function (i) { return +i[idField] === +params.id; });
            var href = item && item.actions && item.actions.view && item.actions.view.href;

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
            var modalId = this.modalId;
            var $wrap = $('#' + modalId);

            if (!$wrap.length) {
                $wrap = $('<div>', { id: modalId }).appendTo('body');
            }

            $wrap.html(
                '<div style="padding:40px;text-align:center;color:#666">' + $t('Loading…') + '</div>'
            );

            var options = {
                type: 'slide',
                title: this.modalTitle ? $t(this.modalTitle) : $t('Details'),
                modalClass: this.modalClass,
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
