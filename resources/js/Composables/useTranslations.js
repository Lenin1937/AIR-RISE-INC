import { usePage } from '@inertiajs/vue3';

export function useTranslations() {
    const page = usePage();
    
    const trans = (key, replacements = {}) => {
        const translations = page.props.translations || {};
        let translation = key.split('.').reduce((obj, k) => obj?.[k], translations) || key;
        
        // Replace placeholders
        Object.keys(replacements).forEach(placeholder => {
            translation = translation.replace(`:${placeholder}`, replacements[placeholder]);
        });
        
        return translation;
    };

    const __ = trans; // Laravel-style alias

    const currentLocale = () => {
        return page.props.locale || 'en';
    };

    const isRTL = () => {
        return currentLocale() === 'ar';
    };

    return {
        trans,
        __,
        currentLocale,
        isRTL
    };
}
