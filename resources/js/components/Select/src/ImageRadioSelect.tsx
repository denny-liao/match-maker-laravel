import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group"
import { Label } from "@/components/ui/label"
import { cn } from "@/lib/utils"


interface Image {
    id: string;
    src: string;
    alt: string;
}

interface ImageRadioSelectProps {
    images: Image[];
    selected: string;
    onSelected: React.ChangeEventHandler;
}

export default function ImageRadioSelect({ images, selected, onSelected }: ImageRadioSelectProps) {

    return (
        <RadioGroup
            value={selected}
            onValueChange={onSelected}
            className="flex flex-wrap gap-4 justify-center"
        >
            {images.map((img) => {
                const isSelected = selected === img.id
                return (
                    <div key={img.id}>
                        {/* 隱藏原本的單選圓點 */}
                        <RadioGroupItem value={img.id} id={img.id} className="sr-only" />

                        {/* 直接點擊圖片的 Label 即可觸發選擇 */}
                        <Label
                            htmlFor={img.id}
                            className={cn(
                                "block cursor-pointer overflow-hidden rounded-xl border-2 transition-all",
                                isSelected
                                    ? "border-primary ring-2 ring-primary/30 scale-105"
                                    : "border-transparent opacity-70 hover:opacity-100"
                            )}
                        >
                            <img
                                src={img.src}
                                alt={img.alt}
                                className="h-24 w-50 object-contain"
                                suppressHydrationWarning
                            />
                        </Label>
                    </div>
                )
            })}
        </RadioGroup>
    )
}