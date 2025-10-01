import { usePage } from '@inertiajs/vue3';

export function useTranslations() {
    const page = usePage();
    
    const t = (key) => {
        const translations = page.props.translations || {};
        const parts = key.split('.');
        let result = translations;
        
        for (let i = 0; i < parts.length; i++) {
            const part = parts[i];
            
            if (result && typeof result === 'object' && part in result) {
                result = result[part];
            } else {
                return key;
            }
        }
        
        return result || key;
    };
    
    return { 
        t,
        locale: page.props.locale || 'id'
    };
}