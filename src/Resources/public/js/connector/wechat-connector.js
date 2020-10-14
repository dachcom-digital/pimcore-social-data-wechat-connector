pimcore.registerNS('SocialData.Connector.Wechat');
SocialData.Connector.Wechat = Class.create(SocialData.Connector.AbstractConnector, {

    hasCustomConfiguration: function () {
        return true;
    },

    afterSaveCustomConfiguration: function () {

        var fieldset = this.customConfigurationPanel.up('fieldset').previousSibling();

        this.changeState(fieldset, 'connection');
    },

    afterChangeState: function (stateType, active) {
        if (stateType === 'connection' && active === true) {
            this.refreshCustomConfigurationPanel();
        }
    },

    beforeDisableFieldState: function (stateType, toDisableState) {

        if (stateType === 'connection' && toDisableState === false) {
            return !(
                this.customConfiguration.hasOwnProperty('appId') &&
                this.customConfiguration.hasOwnProperty('appSecret')
            );
        }

        return toDisableState;
    },

    connectHandler: function (stateType, mainBtn) {

        var stateData = this.states[stateType],
            flag = this.data[stateData.identifier] === true ? 'deactivate' : 'activate';

        // just go by default
        if (flag === 'deactivate') {
            this.stateHandler(stateType, mainBtn);
            return;
        }

        mainBtn.setDisabled(true);

        var win = new Ext.Window({
            width: 400,
            modal: true,
            bodyStyle: 'padding:10px',
            title: t('social_data.connector.wechat.connect_service'),
            html: t('social_data.connector.wechat.connect_service_note'),
            listeners: {
                beforeclose: function () {
                    mainBtn.setDisabled(false);
                }
            },
            buttons: [
                {
                    text: t('social_data.connector.wechat.connect'),
                    iconCls: 'pimcore_icon_open_window',
                    handler: this.handleConnectWindow.bind(this, mainBtn)
                }
            ]
        });

        win.show();
    },

    handleConnectWindow: function (mainBtn, btn) {

        var win = btn.up('window'),
            connectWindow;

        btn.setDisabled(true);
        win.setLoading(true);

        connectWindow = new SocialData.Component.ConnectWindow(
            '/admin/social-data/connector/wechat/connect',
            // success
            function (stateData) {
                win.setLoading(false);
                win.close();
                this.stateHandler('connection', mainBtn);
            }.bind(this),
            // error
            function (stateData) {
                win.setLoading(false);
                btn.setDisabled(false);
                Ext.MessageBox.alert(t('error') + ' ' + stateData.identifier, stateData.description + ' (' + stateData.reason + ')');
            },
            // closed
            function () {
                btn.setDisabled(false);
                win.setLoading(false);
            }
        );

        connectWindow.open();
    },

    getCustomConfigurationFields: function () {

        var data = this.customConfiguration;

        return [
            {
                xtype: 'textfield',
                fieldLabel: t('social_data.connector.wechat.token_expiring_date'),
                disabled: true,
                hidden: !data.hasOwnProperty('accessToken') || data.accessToken === null || data.accessToken === '',
                value: data.hasOwnProperty('accessTokenExpiresAt') ? data.accessTokenExpiresAt === null ? 'never' : data.accessTokenExpiresAt : '--'
            },
            {
                trackResetOnLoad: true,
                xtype: 'textfield',
                name: 'appId',
                fieldLabel: 'App Id',
                allowBlank: false,
                value: data.hasOwnProperty('appId') ? data.appId : null
            },
            {
                trackResetOnLoad: true,
                xtype: 'textfield',
                name: 'appSecret',
                fieldLabel: 'App Secret',
                allowBlank: false,
                value: data.hasOwnProperty('appSecret') ? data.appSecret : null
            },
        ];
    }
});
