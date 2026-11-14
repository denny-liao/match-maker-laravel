<?php

namespace Src\Common\Domain;

abstract class AggregateRoot
{
    // 儲存這個聚合根內部發生的所有領域事件
    private array $recordedEvents = [];

    // 讓內部的實體可以記錄事件（例如：PostWasPublished）
    protected function recordEvent(object $event): void
    {
        $this->recordedEvents[] = $event;
    }

    // 讓外圍的 Repository 在 save() 時把事件拿走並發送出去
    public function pullDomainEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = []; // 釋放後清空
        return $events;
    }
}
