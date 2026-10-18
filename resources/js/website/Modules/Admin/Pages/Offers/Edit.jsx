import { Head } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import OfferForm from '../../Sections/Offers/OfferForm';

export default function Edit({ seo, offer, options }) {
    return (
        <AdminLayout title="Edit offer" current="offers">
            <Head title={seo?.title} />
            <OfferForm
                options={options}
                offer={offer}
                submitUrl={`/admin/offers/${offer.id}`}
                method="put"
                submitLabel="Update offer"
            />
        </AdminLayout>
    );
}
