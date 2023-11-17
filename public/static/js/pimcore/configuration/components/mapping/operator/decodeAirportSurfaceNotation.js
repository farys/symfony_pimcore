/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

pimcore.registerNS("pimcore.plugin.pimcoreDataImporterBundle.configuration.components.mapping.operator.decodeAirportSurfaceNotation");
pimcore.plugin.pimcoreDataImporterBundle.configuration.components.mapping.operator.decodeAirportSurfaceNotation = Class.create(pimcore.plugin.pimcoreDataImporterBundle.configuration.components.mapping.abstractOperator, {

    type: 'decodeAirportSurfaceNotation',

    getMenuGroup: function() {
        return this.menuGroups.dataManipulation;
    },

    getIconClass: function() {
        return "pimcore_icon_operator_splitter";
    },

    getFormItems: function() {
        return [
            {
                xtype: 'textfield',
                fieldLabel: t('plugin_pimcore_datahub_data_importer_configpanel_transformation_pipeline_similarity_threshold'),
                value: this.data.settings ? this.data.settings.similarityThreshold : '70',
                name: 'settings.similarityThreshold',
                listeners: {
                    change: this.inputChangePreviewUpdate.bind(this)
                }
            }
        ];
    }

});