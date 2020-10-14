pimcore.registerNS('SocialData.Feed.Wechat');
SocialData.Feed.Wechat = Class.create(SocialData.Feed.AbstractFeed, {

    panel: null,

    getLayout: function () {

        this.panel = new Ext.form.FormPanel({
            title: false,
            defaults: {
                labelWidth: 200
            },
            items: this.getConfigFields()
        });

        return this.panel;
    },

    getConfigFields: function () {

        var fields = [];

        fields.push(
            {
                xtype: 'numberfield',
                value: this.data !== null ? this.data['count'] : null,
                fieldLabel: t('social_data.wall.feed.wechat.count'),
                name: 'count',
                maxValue: 500,
                minValue: 0,
                labelAlign: 'left',
                anchor: '100%',
                flex: 1
            },
        );

        return fields;
    },

    isValid: function () {
        return this.panel.form.isValid();
    },

    getValues: function () {
        return this.panel.form.getValues();
    }
});
