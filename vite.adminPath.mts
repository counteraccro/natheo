/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Choix du fichier servi pour un import @admin/ du back-office, sans accès au disque
 */

/**
 * Suffixes essayés pour un import écrit sans extension
 */
const SUFFIXES = ['', '.ts', '.vue', '/index.ts'];

/**
 * Fichier servi pour un chemin importé par @admin/ : celui de la surcharge s'il existe, sinon celui du back-office
 * @param path chemin sous Components/Admin
 * @param exists vrai si le fichier existe
 * @param dirs dossiers du back-office et de ses surcharges
 */
export function resolveAdminPath(
    path: string,
    exists: (file: string) => boolean,
    dirs: { admin: string; overwrite: string }
): string {
    const overwrite = SUFFIXES.map((suffix) => `${dirs.overwrite}/${path}${suffix}`).find(exists);
    return overwrite ?? `${dirs.admin}/${path}`;
}
