import { Head } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import OfferForm from '../../Sections/Offers/OfferForm';

export default function Create({ seo, options }) {
    return (
        <AdminLayout title="Create offer" current="offers">
            <Head title={seo?.title} />
            <OfferForm options={options} submitUrl="/admin/offers" method="post" submitLabel="Create offer" />
        </AdminLayout>
    );
}
