/**
 * Permet de faire un copier coller
 * @param textToCopy texte à copier dans le presse-papiers
 */

export const copyToClipboard = async (textToCopy: string): Promise<void> => {
  if (navigator.clipboard && window.isSecureContext) {
    await navigator.clipboard.writeText(textToCopy);
  } else {
    const textArea = document.createElement('textarea');
    textArea.value = textToCopy;
    textArea.style.position = 'absolute';
    textArea.style.left = '-999999px';
    document.body.prepend(textArea);
    textArea.select();
    try {
      document.execCommand('copy');
    } catch (error) {
      console.error(error);
    } finally {
      textArea.remove();
    }
  }
};
