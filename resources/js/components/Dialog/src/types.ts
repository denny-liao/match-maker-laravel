import { DialogEnum } from '@/enums/appEnum';
import { StrictRecord } from '@/types';
import { ReactElement } from 'react';

export type DialogType = DialogEnum;

export interface ConfirmDialogContent {
    title: string;
    description: string;
    text: string;
}

export type ConfirmDialogContentActions = StrictRecord<
    DialogEnum,
    ConfirmDialogContent
>;

export interface ConfirmDialogProps extends Partial<ConfirmDialogContent> {
    type: DialogType;
    onConfirm: () => void;
    processing?: boolean;
    buttonText?: ReactElement;
}
