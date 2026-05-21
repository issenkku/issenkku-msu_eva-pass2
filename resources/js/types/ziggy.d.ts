// ไฟล์ TypeScript ของระบบ: resources/js/types/ziggy.d.ts

import { route } from 'ziggy-js';

declare global {
    let route: typeof route;
}

declare module '@vue/runtime-core' {
    interface ComponentCustomProperties {
        route: typeof route;
    }
}
