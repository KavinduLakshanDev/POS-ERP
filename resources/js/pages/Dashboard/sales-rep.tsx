import AppLayout from '@/layouts/app-layout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { type SharedData } from '@/types';
import { Truck, CreditCard, CheckCircle, Calendar, User, MapPin, DollarSign, Eye } from 'lucide-react';

interface Props extends SharedData {
    stats: any;
}

export default function SalesRepDashboard() {
    const { auth, stats } = usePage<Props>().props;

    const fmt = (v: number | string | undefined | null) => {
        const n = typeof v === 'number' ? v : parseFloat(String(v || 0));
        return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    const markDelivered = (id: number) => {
        if (!confirm('Mark this delivery as delivered?')) return;
        router.patch(`/deliveries/${id}/status`, { status: 'delivered' });
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Sales Rep Dashboard', href: '/dashboard/sales-rep' }]}>
            <Head title="Sales Rep Dashboard" />

            <div className="p-8">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between mb-6">
                    <div>
                        <h1 className="text-2xl font-bold">Sales Representative Dashboard</h1>
                        <p className="text-sm text-slate-500 mt-1">Overview of your deliveries, collections and van stock</p>
                    </div>
                    <div className="text-left lg:text-right">
                        <div className="text-sm font-semibold">{auth.user?.name || ''}</div>
                        <div className="text-xs text-slate-400">Sales Representative</div>
                    </div>
                </div>

                {/* KPIs */}
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div className="bg-white rounded-lg border border-slate-200 p-4 shadow-sm">
                        <div className="text-xs text-slate-500">Total Deliveries</div>
                        <div className="mt-2 text-xl font-bold text-slate-900">{stats.assigned_count}</div>
                    </div>

                    <div className="bg-white rounded-lg border border-slate-200 p-4 shadow-sm">
                        <div className="text-xs text-slate-500">Assigned</div>
                        <div className="mt-2 text-xl font-bold text-amber-600">{stats.assigned_status_count}</div>
                    </div>

                    <div className="bg-white rounded-lg border border-slate-200 p-4 shadow-sm">
                        <div className="text-xs text-slate-500">In Transit</div>
                        <div className="mt-2 text-xl font-bold text-blue-600">{stats.in_transit_count}</div>
                    </div>

                    <div className="bg-white rounded-lg border border-slate-200 p-4 shadow-sm">
                        <div className="text-xs text-slate-500">Delivered</div>
                        <div className="mt-2 text-xl font-bold text-green-600">{stats.delivered_count}</div>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Recent deliveries */}
                    <div className="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="text-lg font-semibold text-slate-800">Your Deliveries</h3>
                            <div className="text-sm text-slate-500">Quick actions: mark delivered · record payment</div>
                        </div>

                        {stats.recent_deliveries && stats.recent_deliveries.length > 0 ? (
                            <>
                                <div className="md:hidden space-y-4 px-4 py-4">
                                    {stats.recent_deliveries.map((d: any) => (
                                        <div key={d.id} className="rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                                            <div className="flex flex-col gap-3">
                                                <div className="flex items-center justify-between gap-4">
                                                    <div>
                                                        <p className="text-sm font-semibold">{d.delivery_number}</p>
                                                        <p className="text-xs text-slate-500">{d.customer_name}</p>
                                                    </div>
                                                    <div className="text-right">
                                                        <p className="text-sm font-semibold">{fmt(d.total_amount)}</p>
                                                        <p className="text-xs text-slate-500">{d.delivery_date ?? '—'}</p>
                                                    </div>
                                                </div>
                                                <div className="flex flex-wrap items-center justify-between gap-3">
                                                    <span className="text-xs font-medium text-slate-700 capitalize">{d.status}</span>
                                                    <div className="flex flex-wrap gap-2">
                                                        <Link href={`/deliveries/${d.id}`} className="text-vismass-blue text-xs font-medium">View</Link>
                                                        {d.status !== 'delivered' && (
                                                            <button onClick={() => markDelivered(d.id)} className="text-green-600 text-xs font-medium">Mark delivered</button>
                                                        )}
                                                        <Link href={`/deliveries/${d.id}#payments`} className="text-slate-600 text-xs font-medium">Record payment</Link>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                                <div className="hidden md:block overflow-x-auto">
                                    <table className="min-w-full divide-y divide-slate-200">
                                        <thead className="bg-slate-50">
                                            <tr>
                                                <th className="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Delivery #</th>
                                                <th className="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Customer</th>
                                                <th className="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Date</th>
                                                <th className="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">Total</th>
                                                <th className="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">Outstanding</th>
                                                <th className="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                                                <th className="px-4 py-2 text-left text-xs font-medium text-slate-500 uppercase">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-slate-200">
                                            {stats.recent_deliveries.map((d: any) => (
                                                <tr key={d.id} className="hover:bg-slate-50">
                                                    <td className="px-4 py-3 text-sm font-medium text-slate-900">{d.delivery_number}</td>
                                                    <td className="px-4 py-3 text-sm text-slate-700">{d.customer_name}</td>
                                                    <td className="px-4 py-3 text-sm text-slate-700">{d.delivery_date ?? '—'}</td>
                                                    <td className="px-4 py-3 text-sm font-medium text-slate-900 text-right">{fmt(d.total_amount)}</td>
                                                    <td className="px-4 py-3 text-sm text-rose-600 text-right">{fmt(d.outstanding)}</td>
                                                    <td className="px-4 py-3 text-sm text-slate-700 capitalize">{d.status}</td>
                                                    <td className="px-4 py-3 text-sm font-medium">
                                                        <div className="flex items-center gap-3 flex-wrap">
                                                            <Link href={`/deliveries/${d.id}`} className="text-vismass-blue" title="View"><Eye className="w-4 h-4" /></Link>
                                                            {d.status !== 'delivered' && (
                                                                <button onClick={() => markDelivered(d.id)} className="text-green-600 text-sm">Mark delivered</button>
                                                            )}
                                                            <Link href={`/deliveries/${d.id}#payments`} className="text-slate-600 text-sm">Record payment</Link>
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </>
                        ) : (
                            <div className="text-center py-12 text-slate-400">No deliveries assigned to you.</div>
                        )}
                    </div>

                    {/* Side panel: Collections & Vehicle */}
                    <aside className="space-y-6">
                        <div className="bg-white rounded-lg border border-slate-200 p-4 shadow-sm">
                            <div className="flex items-center justify-between mb-2">
                                <div className="flex items-center gap-2">
                                    <CreditCard className="w-4 h-4 text-slate-500" />
                                    <div className="text-sm font-semibold">Collections & Returns</div>
                                </div>
                                <div className="text-xs text-slate-400">Today</div>
                            </div>
                            <div className="space-y-2">
                                <div className="flex justify-between items-center">
                                    <span className="text-xs text-slate-500">Collected</span>
                                    <span className="text-sm font-bold text-green-600">LKR {fmt(stats.todays_collections)}</span>
                                </div>
                                <div className="flex justify-between items-center border-t border-slate-50 pt-2">
                                    <span className="text-xs text-slate-500">Shop Returns</span>
                                    <span className="text-sm font-bold text-rose-600">LKR {fmt(stats.todays_returns)}</span>
                                </div>
                                <div className="text-xs text-slate-500 mt-1 border-t border-slate-50 pt-2">
                                    Outstanding: LKR {fmt(stats.outstanding_total)}
                                </div>
                            </div>
                        </div>

                        <div className="bg-white rounded-lg border border-slate-200 p-4 shadow-sm">
                            <div className="flex items-center gap-2 mb-3">
                                <Truck className="w-4 h-4 text-slate-500" />
                                <div className="text-sm font-semibold">Vehicle / Van</div>
                            </div>
                            {stats.vehicle ? (
                                <div>
                                    <div className="text-sm font-medium">{stats.vehicle.name} {stats.vehicle.registration_no ? `(${stats.vehicle.registration_no})` : ''}</div>
                                    <div className="text-xs text-slate-400 mb-2">Stock items (top)</div>
                                    {stats.vehicle.stock && stats.vehicle.stock.length > 0 ? (
                                        <ul className="text-sm space-y-2">
                                            {stats.vehicle.stock.map((vs: any, idx: number) => (
                                                <li key={idx} className="flex justify-between text-slate-700">
                                                    <span className="truncate">{vs.item_ky}{vs.batch_no ? ` · ${vs.batch_no}` : ''}</span>
                                                    <span className="font-semibold">{vs.quantity}</span>
                                                </li>
                                            ))}
                                        </ul>
                                    ) : (
                                        <div className="text-xs text-slate-400">No vehicle stock</div>
                                    )}
                                    <div className="mt-3">
                                        <Link href="/deliveries/vehicles" className="text-sm text-vismass-blue">Manage vehicle stock</Link>
                                    </div>
                                </div>
                            ) : (
                                <div className="text-xs text-slate-400">No vehicle assigned</div>
                            )}
                        </div>
                    </aside>
                </div>
            </div>
        </AppLayout>
    );
}
