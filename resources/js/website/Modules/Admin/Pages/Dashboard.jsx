import { Head } from '@inertiajs/react';
import AdminLayout from '../Layouts/AdminLayout';

export default function Dashboard({ seo, stats = [] }) {
    return (
        <AdminLayout title="Dashboard" current="dashboard">
            <Head title={seo?.title} />
            <section className="admin-stats" aria-label="Store overview">
                {stats.map((stat) => (
                    <article key={stat.label} className="admin-stat">
                        <p className="admin-stat__label">{stat.label}</p>
                        <p className="admin-stat__value">{stat.value}</p>
                    </article>
                ))}
            </section>
            <section className="admin-panel">
                <h2 className="admin-panel__title">Getting started</h2>
                <p className="admin-panel__text">
                    Admin modules are ready. Next you can add catalog, order, and customer
                    management screens under /admin.
                </p>
            </section>
        </AdminLayout>
    );
}
