/**
 * Créer un cookie en JS
 * @param name
 * @param value
 * @param expDays
 * @return {Promise<void>}
 */
export const setCookie = async (name, value, expDays) => {
  const d = new Date();
  d.setTime(d.getTime() + expDays * 24 * 60 * 60 * 1000);
  let expires = 'expires=' + d.toUTCString();
  document.cookie = name + '=' + value + ';' + expires + ';path=/';
};
