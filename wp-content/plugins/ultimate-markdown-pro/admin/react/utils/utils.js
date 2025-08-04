/**
 * Download a file from the provided string.
 *
 * @param content
 * @param fileNamePrefix
 */
export const downloadFileFromString = (content, fileNamePrefix) => {

    const blob = content;

    // Create a temporary URL to the blob.
    const url = window.URL.createObjectURL(new Blob([blob]));

    // Create a link element.
    const link = document.createElement('a');
    link.href = url;
    const fileName = fileNamePrefix + '-' + Date.now().toString().slice(0, 10) + '.csv';
    link.setAttribute('download', fileName); // Specify the filename

    // Append the link to the body.
    document.body.appendChild(link);

    // Trigger the click event on the link.
    link.click();

    // Cleanup.
    link.parentNode.removeChild(link);

}