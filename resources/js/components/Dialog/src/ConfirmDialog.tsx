import React, { useState } from 'react';
import { Trash2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import type { ConfirmDialogContentActions, ConfirmDialogProps } from './types';
import { DialogEnum } from '@/enums/appEnum';

const dialogContent: ConfirmDialogContentActions = {
    [DialogEnum.DELETE]: {
        title: '確定要刪除嗎？',
        description: '此操作無法復原，刪除後資料將永久移除。',
        text: '刪除'
    },
}


export function ConfirmDialog({
    type,
    onConfirm,
    processing = false,
    buttonText =  <Trash2 className="size-3.5" />,
}: ConfirmDialogProps) {
    const [open, setOpen] = useState(false);
    const {title, description, text } = dialogContent[type]

    const handleConfirm = () => {
        onConfirm();
        setOpen(false);
    };

    return (
        <AlertDialog open={open} onOpenChange={setOpen}>
            <AlertDialogTrigger
                render={
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        className="size-7 text-muted-foreground hover:text-destructive"
                    >
                        {buttonText}
                        <span className="sr-only">{text}</span>
                    </Button>
                }
            />

            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>{title}</AlertDialogTitle>
                    <AlertDialogDescription>{description}</AlertDialogDescription>
                </AlertDialogHeader>

                <AlertDialogFooter>
                    <AlertDialogCancel disabled={processing}>取消</AlertDialogCancel>
                    <AlertDialogAction
                        onClick={handleConfirm}
                        disabled={processing}
                        className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
                    >
                        {processing ? '處理中...' : `確定${text}`}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}
