/**
 * This file is used to create the "Submit Markdown" editor sidebar section.
 *
 * @package ultimate-markdown-pro
 */

const {Button, TextareaControl} = wp.components;
const {dispatch, select} = wp.data;
const {PluginDocumentSettingPanel} = wp.editor;
const {Component} = wp.element;
const {__} = wp.i18n;
import {updateFields} from '../../utils';

export default class Sidebar extends Component {

    constructor(props) {

        super(...arguments);

        // The state is used only to rerender the component with setState.
        this.state = {
            textareaValue: '',
        };

    }

    render() {

        const meta = select('core/editor').getEditedPostAttribute('meta');
        const markdownText = meta['_import_markdown_pro_submit_text_textarea'];

        return (
            <PluginDocumentSettingPanel
                name="submit-text"
                title={__('Submit Markdown', 'ultimate-markdown-pro')}
                className="daextulmap-submit-text-panel"
            >


                <TextareaControl
                    label={__('Markdown text', 'ultimate-markdown-pro')}
                    help={__(
                        'Enter the Markdown text, then click the submit text button to generate the corresponding blocks.',
                        'ultimate-markdown-pro')}
                    value={markdownText}
                    onChange={(value) => {

                        dispatch('core/editor').editPost({
                            meta: {
                                '_import_markdown_pro_submit_text_textarea': value,
                            },
                        });

                        // Used to rerender the component.
                        this.setState({
                            textareaValue: value,
                        });
                    }}
                    __nextHasNoMarginBottom={ true }
                />

                <Button
                    variant="secondary"
                    className="editor-post-trash is-destructive"
                    onClick={() => {

                        wp.ajax.post('daextulmap_submit_markdown', {
                            security: window.DAEXTULMAP_PARAMETERS.nonce,
                            markdowntext: this.state.textareaValue,
                        }).done((data) => {

                            let pageHtml = '';
                            if ('marked' === window.DAEXTULMAP_PARAMETERS.editorMarkdownParser) {
                                pageHtml = marked(data['content']);
                            } else {
                                pageHtml = data['html_content'];
                            }

                            pageHtml = DOMPurify.sanitize(pageHtml, { USE_PROFILES: { html: true } });

                            wp.data.dispatch('core/block-editor').resetBlocks([]);

                            const blocks = wp.blocks.rawHandler({ HTML: pageHtml });

                            updateFields(blocks, data);

                            dispatch('core/editor').editPost({
                                meta: {
                                    '_import_markdown_pro_submit_text_textarea': '',
                                },
                            });

                            this.setState({
                                textareaValue: '',
                            });

                            wp.data.dispatch('core/notices').createErrorNotice(
                                __('Markdown content successfully processed and blocks created.', 'ultimate-markdown-pro'),
                                {
                                    type: 'snackbar',
                                    isDismissible: true,
                                }
                            );

                        }).fail((data) => {

                            const errorMessages = {
                                invalid_date_format: __('Invalid date format in Front Matter. Please enter a valid date, e.g. 2025-07-16, or a date and time, e.g. 2025-07-16 10:30:42.', 'ultimate-markdown-pro'),
                                generic_error: __('Please review your document for any formatting issues in the Front Matter or Markdown content, then try again. The content could not be processed. For more details on supported fields and formatting, please refer to the plugin documentation.', 'ultimate-markdown-pro'),
                            };

                            const errorMessage = errorMessages[data['error']] || errorMessages['generic_error'];

                            wp.data.dispatch('core/notices').createErrorNotice(
                                errorMessage,
                                {
                                    type: 'snackbar',
                                    isDismissible: true,
                                }
                            );
                        });

                    }}
                >{__('Submit text', 'ultimate-markdown-pro')}</Button>

            </PluginDocumentSettingPanel>
        );
    }
}