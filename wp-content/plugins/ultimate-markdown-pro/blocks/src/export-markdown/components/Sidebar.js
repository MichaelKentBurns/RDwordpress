/**
 * This file is used to create the "Export Markdown" editor sidebar section.
 *
 * @package ultimate-markdown-pro
 */

const {Button} = wp.components;
const {select} = wp.data;
const {PluginDocumentSettingPanel} = wp.editor;
const {Component} = wp.element;
const {__} = wp.i18n;
import {downloadFileFromString} from '../../utils';

export default class Sidebar extends Component {

    render() {

        const currentPostId = select('core/editor').getCurrentPostId();

        return (
            <PluginDocumentSettingPanel
                name="export-markdown"
                title={__('Export Markdown', 'ultimate-markdown-pro')}
            >

                <Button
                    variant="secondary"
                    onClick={() => {

                        /**
                         * Initialize the options fields with the data received from the REST API
                         * endpoint provided by the plugin.
                         */
                        wp.apiFetch({
                            path: '/ultimate-markdown-pro/v1/export-post',
                            method: 'POST',
                            data: {
                                post_id: currentPostId,
                            },
                        }).then(data => {

                                downloadFileFromString(data.content, window.DAEXTULMAP_PARAMETERS.exportedFilesExtension, currentPostId);

                                wp.data.dispatch('core/notices').createErrorNotice(
                                    __('Post content successfully processed and Markdown file saved.', 'ultimate-markdown-pro'),
                                    {
                                        type: 'snackbar',
                                        isDismissible: true,
                                    }
                                );

                            },
                        );

                    }}
                >{__('Export', 'ultimate-markdown-pro')}</Button>

            </PluginDocumentSettingPanel>
        );
    }

}