import React, { useState } from 'react';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ShieldCheck } from 'lucide-react';
import { t } from '@/lib/i18n';
import axios from 'axios';

interface AdminAuthModalProps {
    isOpen: boolean;
    onClose: () => void;
    onSuccess: () => void;
    saleDetails?: {
        invoice_no: string;
        customer_name: string;
        total_amount: number | string;
    };
    saleId?: number; // Optional, if we want to authorize a specific sale edit
    title?: string;
    description?: string;
}

export default function AdminAuthModal({
    isOpen,
    onClose,
    onSuccess,
    saleDetails,
    saleId,
    title = 'Admin Authorization Required',
    description = 'Enter admin credentials to proceed.'
}: AdminAuthModalProps) {
    const [adminEmail, setAdminEmail] = useState('');
    const [adminPassword, setAdminPassword] = useState('');
    const [adminError, setAdminError] = useState('');
    const [verifying, setVerifying] = useState(false);

    const handleConfirm = async () => {
        if (!adminEmail || !adminPassword) {
            setAdminError(t('Please enter admin email and password.'));
            return;
        }

        setVerifying(true);
        setAdminError('');
        try {
            await axios.post('/sales/verify-admin', {
                email: adminEmail,
                password: adminPassword,
                sale_id: saleId || null,
            });

            // Credentials valid
            setAdminEmail('');
            setAdminPassword('');
            onSuccess();
        } catch (err: any) {
            const msg = err?.response?.data?.message || t('Invalid admin credentials.');
            setAdminError(msg);
        } finally {
            setVerifying(false);
        }
    };

    return (
        <Dialog open={isOpen} onOpenChange={(open) => {
            if (!open) {
                setAdminEmail('');
                setAdminPassword('');
                setAdminError('');
                onClose();
            }
        }}>
            <DialogContent className="sm:max-w-[440px]">
                <DialogHeader>
                    <div className="flex items-center space-x-2 mb-1">
                        <ShieldCheck className="h-5 w-5 text-vismass-blue" />
                        <DialogTitle>{t(title)}</DialogTitle>
                    </div>
                    <DialogDescription>
                        {t(description)}
                    </DialogDescription>
                </DialogHeader>

                {saleDetails && (
                    <div className="p-3 bg-slate-50 rounded-lg border border-slate-200 text-sm space-y-1">
                        <p><span className="font-medium text-slate-600">{t('Invoice')}:</span> {saleDetails.invoice_no}</p>
                        <p><span className="font-medium text-slate-600">{t('Customer')}:</span> {saleDetails.customer_name}</p>
                        <p><span className="font-medium text-slate-600">{t('Total')}:</span> Rs. {Number(saleDetails.total_amount).toFixed(2)}</p>
                    </div>
                )}

                <div className="space-y-4 mt-2">
                    <div className="space-y-1.5">
                        <Label htmlFor="admin-email" className="text-sm font-medium">{t('Admin Email')} <span className="text-red-500">*</span></Label>
                        <Input
                            id="admin-email"
                            type="email"
                            value={adminEmail}
                            onChange={(e) => { setAdminEmail(e.target.value); setAdminError(''); }}
                            onKeyDown={(e) => e.key === 'Enter' && handleConfirm()}
                            placeholder="admin@example.com"
                            autoComplete="off"
                        />
                    </div>
                    <div className="space-y-1.5">
                        <Label htmlFor="admin-password" className="text-sm font-medium">{t('Admin Password')} <span className="text-red-500">*</span></Label>
                        <Input
                            id="admin-password"
                            type="password"
                            value={adminPassword}
                            onChange={(e) => { setAdminPassword(e.target.value); setAdminError(''); }}
                            onKeyDown={(e) => e.key === 'Enter' && handleConfirm()}
                            placeholder="••••••••"
                            autoComplete="new-password"
                        />
                    </div>
                    {adminError && (
                        <p className="text-sm text-red-600 font-medium">{adminError}</p>
                    )}
                </div>

                <DialogFooter className="mt-2">
                    <Button variant="outline" onClick={onClose} disabled={verifying}>
                        {t('Cancel')}
                    </Button>
                    <Button onClick={handleConfirm} disabled={verifying} className="bg-vismass-blue hover:bg-vismass-blue/90">
                        {verifying ? (
                            <span className="flex items-center space-x-1">
                                <span className="h-3.5 w-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin inline-block" />
                                <span>{t('Verifying...')}</span>
                            </span>
                        ) : (
                            <span className="flex items-center space-x-1">
                                <ShieldCheck className="h-4 w-4" />
                                <span>{t('Authorize')}</span>
                            </span>
                        )}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
