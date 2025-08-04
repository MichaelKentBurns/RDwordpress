/**
 * This file is used to create the "Load Markdown" editor sidebar section.
 *
 * @package ultimate-markdown-pro
 */

const {dispatch, select} = wp.data;
const {PluginDocumentSettingPanel} = wp.editor;
const {Component} = wp.element;
const {__} = wp.i18n;
const {Button, SelectControl} = wp.components;
import {updateFields} from '../../utils';

export default class Sidebar extends Component {

    constructor(props) {

        super(...arguments);

        // The state is only used to rerender the component with setState.
        this.state = {
            documentIdSelectorValue: '',
        };

    }

    render() {

        const meta = select('core/editor').getEditedPostAttribute('meta');
        const documentIdSelectorMeta = meta['_import_markdown_pro_load_document_selector'];

        return (
            <PluginDocumentSettingPanel
                name="daextulmap-load-document"
                title={__('Load Markdown', 'ultimate-markdown-pro')}
                className="daextulmap-load-document-panel"
            >
                <SelectControl
                    label={__('Markdown document', 'ultimate-markdown-pro')}
                    help={__(
                        'Select a Markdown document, then click the submit document button to generate the corresponding blocks.',
                        'ultimate-markdown-pro')}
                    value={documentIdSelectorMeta}
                    onChange={(document_id) => {

                        dispatch('core/editor').editPost({
                            meta: {
                                '_import_markdown_pro_load_document_selector': document_id,
                            },
                        });

                        // Used to rerender the component.
                        this.setState({
                            documentIdSelectorValue: document_id,
                        });

                    }}
                    options={window.DAEXTULMAP_PARAMETERS.documents}
                    __nextHasNoMarginBottom={ true }
                    __next40pxDefaultSize={ true }
                />

                <Button
                    variant="secondary"
                    className="editor-post-trash is-destructive"
                    onClick={() => {

                        // Send an AJAX request to retrieve the HTML of the selected markdown document ----------------.

                        // Get the meta value.
                        const meta = select('core/editor').getEditedPostAttribute('meta');
                        const document_id = meta['_import_markdown_pro_load_document_selector'];

                        // Do not proceed if the selected option is "Not set".
                        if (parseInt(document_id, 10) === 0) {
                            return;
                        }

                        // Send an AJAX request to retrieve the HTML of the selected document.
                        const data = new FormData();
                        data.append('action', 'daextulmap_load_document');
                        data.append('security', window.DAEXTULMAP_PARAMETERS.nonce);
                        data.append('document_id', document_id);

                        fetch(window.DAEXTULMAP_PARAMETERS.ajaxUrl, {
                            method: 'POST',
                            body: data,
                        }).then((response) => {

                            return response.json();

                        }).then((response) => {

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
                             * Delete the content of the post (based on the related option).
                             * See: https://wordpress.stackexchange.com/questions/305932/gutenberg-remove-add-blocks-with-custom-script
                             */
                            wp.data.dispatch('core/block-editor').resetBlocks([]);

                            // Generate the blocks from the HTML.
                            const blocks = wp.blocks.rawHandler(
                                {HTML: pageHtml},
                            );

                            // Update the editor fields.
                            updateFields(blocks, response.data);

                            // Reset the document selector to "Not set" (0) in meta.
                            dispatch('core/editor').editPost({
                                meta: {
                                    '_import_markdown_pro_load_document_selector': '0',
                                },
                            });

                            // Reset the document selector to "Not set" (0) in local state.
                            this.setState({
                                documentIdSelectorValue: '0',
                            });

                            wp.data.dispatch('core/notices').createErrorNotice(
                                __('Markdown content successfully processed and blocks created.', 'ultimate-markdown-pro'),
                                {
                                    type: 'snackbar',
                                    isDismissible: true,
                                }
                            );

                        });

                    }}
                >{__('Submit document', 'ultimate-markdown-pro')}</Button>

            </PluginDocumentSettingPanel>
        );
    }
}