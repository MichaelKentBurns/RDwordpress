/**
 * This file is used to store utility functions used in the block editor.
 *
 * @package ultimate-markdown-pro
 */

/**
 * Updates the editor fields based on the provided data.
 *
 * @param blocks The blocks that should be added in the block editor
 * @param data The data used to update various fields of the editor
 */
export function updateFields(blocks, data) {

    const {dispatch, select} = wp.data;

    // Insert the blocks in the editor.
    wp.data.dispatch('core/block-editor').insertBlocks(blocks);

    // Clear selection so the Block tab doesn't stay active.
    wp.data.dispatch('core/block-editor').clearSelectedBlock();

    // Force the sidebar back to "Post" section.
    wp.data.dispatch('core/edit-post').openGeneralSidebar('edit-post/document');

    // Update the thumbnail.
    if (data['thumbnail'] !== null) {
        updateThumbnail(data['thumbnail']);
    }

    // Update the post title.
    if (data['title'] !== null) {
        wp.data.dispatch('core/editor').editPost({title: data['title']});
    }

    // Update the excerpt.
    if (data['excerpt'] !== null) {
        wp.data.dispatch('core/editor').editPost({excerpt: data['excerpt']});
    }

    // Update the categories.
    if (data['categories'] !== null) {
        // Replace all categories with the new ones.
        dispatch('core/editor').editPost({ categories: data['categories'] });
    }

    // Update the tags.
    if (data['tags'] !== null) {
        // Replace all tags with the new ones.
        dispatch('core/editor').editPost({ tags: data['tags'] });
    }

    // Update the author.
    if (data['author'] !== null) {
        wp.data.dispatch('core/editor').editPost({author: data['author']});
    }

    // Update the date.
    if (data['date'] !== null) {
        wp.data.dispatch('core/editor').editPost({date: data['date']});
    }

    // Update the status.
    if (data['status'] !== null) {
        wp.data.dispatch('core/editor').editPost({status: data['status']});
    }

    // pdate the slug.
    if (data['slug'] !== null) {
        wp.data.dispatch('core/editor').editPost({slug: data['slug']});
    }

}

/**
 * Update the thumbnail based on the provided value.
 *
 * Note that it's currently accepted only the thumbnail ID.
 *
 * @param thumbnailId
 */
export function updateThumbnail(thumbnailId) {

    // Get the post editor instance.
    const postEditor = wp.data.select('core/editor');

    // Get the current post data.
    const currentPost = postEditor.getCurrentPost();

    // Update the thumbnail ID in the post data.
    const updatedPostData = Object.assign({}, currentPost, {
        featured_media: thumbnailId,
    });

    // Update the post with the new data.
    wp.data.dispatch('core/editor').editPost(updatedPostData);

}

/**
 * Download a file from the provided string.
 *
 * @param content
 * @param fileNameExtension
 */
export const downloadFileFromString = (content, fileNameExtension, postId) => {

    const blob = content;

    // Create a temporary URL to the blob.
    const url = window.URL.createObjectURL(new Blob([blob]));

    // Create a link element.
    const link = document.createElement('a');
    link.href = url;
    const fileName = 'post-' + postId + '.' + fileNameExtension;
    link.setAttribute('download', fileName); // Specify the filename

    // Append the link to the body.
    document.body.appendChild(link);

    // Trigger the click event on the link.
    link.click();

    // Cleanup.
    link.parentNode.removeChild(link);

}