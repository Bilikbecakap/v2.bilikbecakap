import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function usePermissions() {
    const page = usePage();
    
    const user = computed(() => page.props.auth.user);
    
    const can = (permission) => {
        if (!user.value) return false;
        
        // Check if user is super-admin (bypass all checks)
        if (user.value.roles?.includes('super-admin')) {
            return true;
        }
        
        // Check if user has the specific permission
        return user.value.permissions?.includes(permission) || false;
    };
    
    const hasRole = (role) => {
        if (!user.value) return false;
        return user.value.roles?.includes(role) || false;
    };
    
    return {
        can,
        hasRole,
        user
    };
}