import { Head } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import BannerForm from '../../Sections/Banners/BannerForm';

export default function Edit({ seo, banner, options }) {
    return (
        <AdminLayout title="Edit Banner" current="banners">
            <Head title={seo?.title} />
            <BannerForm
                options={options}
                banner={banner}
                submitUrl={`/admin/banners/${banner.id}`}
                method="put"
                submitLabel="Update Banner"
            />
        </AdminLayout>
    );
}
