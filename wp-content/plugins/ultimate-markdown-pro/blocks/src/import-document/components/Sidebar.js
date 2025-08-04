/**
 * This file is used to create the "Import Document" editor sidebar section.
 *
 * @package ultimate-markdown-pro
 */

const {PluginDocumentSettingPanel} = wp.editor;
const {Component} = wp.element;
const {__} = wp.i18n;
const {FormFileUpload} = wp.components;
import {updateFields} from '../../utils';

export default class Sidebar extends Component {
    render() {

        return (
            <PluginDocumentSettingPanel
                name="daextulmap-import-document"
                title={__('Import Markdown', 'ultimate-markdown-pro')}
            >
                <FormFileUpload
                    className="block-library-gallery-add-item-button"
                    onChange={() => {

                        // Store the data of all the uploaded files.
                        const files = event.target.files;

                        // Store the data of the first uploaded file.
                        const file = files[0];

                        // Send ajax request.
                        const data = new FormData();
                        data.append('action', 'daextulmap_import_document');
                        data.append('security', window.DAEXTULMAP_PARAMETERS.nonce);
                        data.append('uploaded_file', file);

                        fetch(window.DAEXTULMAP_PARAMETERS.ajaxUrl, {
                            method: 'POST',
                            body: data,
                        }).then(function (response) {

                            return response.json();

                        }).then(function (response) {

                            // Map known error codes to user-friendly messages.
                            const errorMessages = {
                                invalid_date_format: __('Invalid date format in Front Matter. Please enter a valid date, e.g. 2025-07-16, or a date and time, e.g. 2025-07-16 10:30:42.', 'ultimate-markdown-pro'),
                                generic_error: __('Please review your document for any formatting issues in the Front Matter or Markdown content, then try again. The content could not be processed. For more details on supported fields and formatting, please refer to the plugin documentation.', 'ultimate-markdown-pro'),
                            };

                            // Fallback to a generic error string if the error code is not mapped.
                            const errorMessage = errorMessages[response.data['error']] || errorMessages['generic_error'];

                            /**
                             * If response['error'] is set, it means that an error occurred. In that case we display an
                             * error message and stop the execution.
                             */
                            if (response.data['error']) {
                                // Display an error message.
                                wp.data.dispatch('core/notices').createErrorNotice(
                                    errorMessage,
                                    {
                                        type: 'snackbar',
                                        isDismissible: true,
                                    }
                                );
                                return;
                            }

                            // Convert the Markdown text to HTML.
                            let pageHtml = '';
                            if('marked' === window.DAEXTULMAP_PARAMETERS.editorMarkdownParser) {
                                pageHtml = marked(response.data['content']);
                            }else{
                                pageHtml = response.data['html_content'];
                            }

                            // Sanitize the generated HTML.
                            pageHtml = DOMPurify.sanitize(pageHtml,
                                {USE_PROFILES: {html: true}});

                            /**
                             * Delete the content of the post.
                             * See: https://wordpress.stackexchange.com/questions/305932/gutenberg-remove-add-blocks-with-custom-script
                             */
                            wp.data.dispatch('core/block-editor').resetBlocks([]);

                            // Generate the blocks from the HTML.
                            const blocks = wp.blocks.rawHandler(
                                {HTML: pageHtml},
                            );

                            // Update the editor fields.
                            updateFields(blocks, response.data);

                            wp.data.dispatch('core/notices').createErrorNotice(
                                __('Markdown content successfully processed and blocks created.', 'ultimate-markdown-pro'),
                                {
                                    type: 'snackbar',
                                    isDismissible: true,
                                }
                            );

                        });

                    }}
                    accept=".md,.markdown,.mdown,.mkdn,.mkd,.mdwn,.mdtxt,.mdtext,.text,.txt"
                    icon="insert"
                    __next40pxDefaultSize={true}
                >
                    {__('Upload file and import', 'ultimate-markdown-pro')}
                </FormFileUpload>
            </PluginDocumentSettingPanel>
        );
    }
}