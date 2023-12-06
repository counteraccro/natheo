/**
 * Permet de créer un debounce
 * @author Gourdon Aymeric
 * @version 1.0
 */

export const debounce = (func, delay = 600, immediate = false) => {
    let timeout
    return function () {
        const context = this
        const args = arguments
        const later = function () {
            timeout = null
            if (!immediate) func.apply(context, args)
        }
        const callNow = immediate && !timeout
        clearTimeout(timeout)
        timeout = setTimeout(later, delay)
        if (callNow) func.apply(context, args)
    }
}