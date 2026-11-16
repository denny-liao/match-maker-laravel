import { Button } from "@/components/ui/button";
import { TextareaButtonEnum } from "@/enums/appEnum";
import { SetDataAction } from "@inertiajs/react";


export type ButtonType = TextareaButtonEnum

export interface ButtonContent {
     type: ButtonType;
     processingText?: string;
     text?: string;
     attrs?: React.ComponentProps<typeof Button>; 
}

export interface BasicTextareaProps {
    data: Record<"message", string>;
    onChange: SetDataAction<{ message: string }>;
    processing: boolean;
    errors: Record<any, string>;
    buttonContent: Array<ButtonContent>;
}
