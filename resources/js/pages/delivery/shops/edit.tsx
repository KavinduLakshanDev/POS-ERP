import AppLayout from '@/layouts/app-layout';
import InputError from '@/components/input-error';
import { Head, useForm } from '@inertiajs/react';
import { Store, ArrowLeft } from 'lucide-react';
import { FormEventHandler } from 'react';

export default function ShopEdit({ shop, routes = [] }: { shop: any; routes?: any[] }) {
    const { data, setData, put, processing, errors } = useForm({
        name: shop.name ?? '',
        address: shop.address ?? '',
        contact_phone: shop.contact_phone ?? '',
        delivery_route_id: shop.delivery_route_id ? String(shop.delivery_route_id) : '',
        is_active: shop.is_active ? '1' : '0',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(`/deliveries/shops/${shop.id}`);
    };

    return (
        <AppLayout breadcrumbs={[
            { title: 'Shops', href: '/deliveries/shops' },
            { title: 'Edit Shop', href: `/deliveries/shops/${shop.id}/edit` },
        ]}>
            <Head title="Edit Shop" />

            <div className="min-h-screen bg-slate-50">
                <header className="bg-gradient-to-r from-vismass-blue to-vismass-grey shadow">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="flex items-center space-x-3 py-4">
                            <button onClick={() => window.history.back()} className="rounded-lg bg-white/20 p-2 hover:bg-white/30 transition">
                                <ArrowLeft className="h-5 w-5 text-white" />
                            </button>
                            <div className="rounded-lg bg-white/20 p-2 shadow">
                                <Store className="h-5 w-5 text-white" />
                            </div>
                            <div>
                                <h1 className="text-xl font-bold text-white">Edit Shop</h1>
                                <p className="text-xs text-white/80">{shop.name}</p>
                            </div>
                        </div>
                    </div>
                </header>

                <main className="mx-auto max-w-2xl py-8 px-4 sm:px-6 lg:px-8">
                    <div className="bg-white rounded-2xl shadow-lg border border-slate-200 p-8">
                        <form onSubmit={submit} className="space-y-6">
                            <div className="space-y-2">
                                <label className="text-sm font-medium text-slate-700">Shop Name *</label>
                                <input
                                    type="text"
                                    value={data.name}
                                    onChange={e => setData('name', e.target.value)}
                                    className="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-vismass-blue focus:outline-none focus:ring-1 focus:ring-vismass-blue"
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="space-y-2">
                                <label className="text-sm font-medium text-slate-700">Address</label>
                                <textarea
                                    value={data.address}
                                    onChange={e => setData('address', e.target.value)}
                                    rows={3}
                                    className="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-vismass-blue focus:outline-none focus:ring-1 focus:ring-vismass-blue"
                                />
                                <InputError message={errors.address} />
                            </div>

                            <div className="space-y-2">
                                <label className="text-sm font-medium text-slate-700">Contact Phone</label>
                                <input
                                    type="tel"
                                    value={data.contact_phone}
                                    onChange={e => {
                                        const val = e.target.value;
                                        if (/^\d{0,10}$/.test(val)) {
                                            setData('contact_phone', val);
                                        }
                                    }}
                                    maxLength={10}
                                    className="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-vismass-blue focus:outline-none focus:ring-1 focus:ring-vismass-blue"
                                    placeholder="0770235698"
                                />
                                <InputError message={errors.contact_phone} />
                            </div>

                            <div className="space-y-2">
                                <label className="text-sm font-medium text-slate-700">Delivery Route *</label>
                                <select
                                    value={data.delivery_route_id}
                                    onChange={e => setData('delivery_route_id', e.target.value)}
                                    required
                                    className="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-vismass-blue focus:outline-none focus:ring-1 focus:ring-vismass-blue"
                                >
                                    <option value="">Select a route</option>
                                    {routes.map((r: any) => (
                                        <option key={r.id} value={r.id}>{r.name}</option>
                                    ))}
                                </select>
                                <InputError message={errors.delivery_route_id} />
                            </div>

                            <div className="space-y-2">
                                <label className="text-sm font-medium text-slate-700">Status</label>
                                <select
                                    value={data.is_active}
                                    onChange={e => setData('is_active', e.target.value)}
                                    className="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-vismass-blue focus:outline-none focus:ring-1 focus:ring-vismass-blue"
                                >
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <div className="flex gap-3 pt-2">
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="flex-1 rounded-lg bg-vismass-blue px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-vismass-blue/90 disabled:opacity-60 transition"
                                >
                                    {processing ? 'Saving...' : 'Update Shop'}
                                </button>
                                <button
                                    type="button"
                                    onClick={() => window.history.back()}
                                    className="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </main>
            </div>
        </AppLayout>
    );
}
