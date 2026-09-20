import { Head } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import BannerForm from '../../Sections/Banners/BannerForm';

export default function Create({ seo, options }) {
    return (
        <AdminLayout title="Create Banner" current="banners">
            <Head title={seo?.title} />
            <BannerForm
                options={options}
                submitUrl="/admin/banners"
                method="post"
                submitLabel="Create Banner"
            />
        </AdminLayout>
    );
}
