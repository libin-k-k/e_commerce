import { Head, usePage } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import CategoryForm from '../../Sections/Categories/CategoryForm';

export default function Create({ seo, options }) {
    const { url } = usePage();
    const parentFromQuery = new URL(url, 'http://localhost').searchParams.get('parent') ?? '';

    const optionsWithParent = {
        ...options,
        parents: options.parents,
    };

    return (
        <AdminLayout title="Create Category" current="categories">
            <Head title={seo?.title} />
            <CategoryForm
                options={optionsWithParent}
                category={parentFromQuery ? { parent_id: Number(parentFromQuery) } : null}
                submitUrl="/admin/categories"
                method="post"
                submitLabel="Create Category"
            />
        </AdminLayout>
    );
}
