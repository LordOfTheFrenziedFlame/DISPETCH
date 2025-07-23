<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection as SupportCollection;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    // Константы для eager loading паттернов
    const ATTACHMENTS_RELATIONS = [
        'attachments',
        'measurement.attachments',
        'contract.attachments',
        'documentation.attachments'
    ];

    const ORDER_ATTACHMENTS_RELATIONS = [
        'order.attachments',
        'order.measurement.attachments',
        'order.contract.attachments',
        'order.documentation.attachments'
    ];

    protected $fillable = ['manager_id', 'customer_name', 'address', 'meeting_at', 'phone_number', 'email', 'order_number', 'status', 'surveyor_id', 'constructor_id', 'installer_id', 'notes', 'total_amount', 'additional_data', 'product_name'];

    protected $casts = [
        'meeting_at' => 'datetime',
    ];

    public function manager() {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function surveyor() {
        return $this->belongsTo(User::class, 'surveyor_id');
    }

    public function constructor() {
        return $this->belongsTo(User::class, 'constructor_id');
    }

    public function installer() {
        return $this->belongsTo(User::class, 'installer_id');
    }

    public function measurement() {
        return $this->hasOne(\App\Models\Measurement::class)->withTrashed();
    }

    public function contract() {
        return $this->hasOne(Contract::class);
    }

    public function documentation() {
        return $this->hasOne(Documentation::class);
    }

    public function production() {
        return $this->hasOne(Production::class);
    }

    public function installation() {
        return $this->hasOne(Installation::class);
    }

    public function attachments() {
        return $this->morphMany(Attachment::class, 'attachable');
    }


    public function getAllAttachmentsAttribute() : SupportCollection {
        // ВНИМАНИЕ: Этот метод может вызывать N+1 проблемы!
        // 
        // ДЛЯ ОДИНОЧНЫХ ЗАПИСЕЙ используйте:
        // $order = Order::withAllAttachments()->find($id);
        // $attachments = $order->all_attachments;
        //
        // ДЛЯ СПИСКОВ ЗАПИСЕЙ используйте eager loading в контроллерах:
        // Measurements: ->with(['order.attachments', 'order.measurement.attachments', 'order.contract.attachments', 'order.documentation.attachments'])
        // Contracts: ->with(['order.attachments', 'order.measurement.attachments', 'order.contract.attachments', 'order.documentation.attachments'])  
        // Documentation: ->with(['order.attachments', 'order.measurement.attachments', 'order.contract.attachments', 'order.documentation.attachments'])
        // Installations: ->with(['order.attachments', 'order.measurement.attachments', 'order.contract.attachments', 'order.documentation.attachments'])
        // Productions: ->with(['order.attachments', 'order.measurement.attachments', 'order.contract.attachments', 'order.documentation.attachments'])
        //
        // ИЛИ используйте новые scope методы:
        // Order::withFullData()->get() - для полной загрузки
        // Order::withAttachmentsOnly()->get() - только для attachments
        
        return collect()
            ->merge($this->attachments ?? [])
            ->merge($this->measurement?->attachments ?? [])
            ->merge($this->contract?->attachments ?? [])
            ->merge($this->documentation?->attachments ?? []);
    }

    /**
     * Альтернативный метод для получения всех вложений с правильным eager loading
     */
    public static function withAllAttachments()
    {
        return static::with(static::ATTACHMENTS_RELATIONS);
    }

    /**
     * Scope для безопасной загрузки всех связанных данных включая attachments
     */
    public function scopeWithFullData($query)
    {
        return $query->with(array_merge([
            'manager',
            'surveyor', 
            'constructor',
            'installer',
            'measurement',
            'contract',
            'documentation',
            'installation',
            'production'
        ], static::ATTACHMENTS_RELATIONS));
    }

    /**
     * Scope для загрузки только attachments без других связей
     */
    public function scopeWithAttachmentsOnly($query)
    {
        return $query->with(static::ATTACHMENTS_RELATIONS);
    }


    public function getProductionStageAttribute()
    {
        if ($this->status === 'completed') {
            return 'Этап завершён: Заказ полностью завершён';
        }

        // --- Финальный этап ---
        if ($this->installation) {
            if ($this->installation->installed_at) {
                return 'Этап завершён: Установка';
            }
            return 'Этап в процессе: Установка';
        }

        // --- Производство ---
        if ($this->production) {
            if ($this->production->completed_at) {
                return 'Этап завершён: Производство';
            }
            return 'Этап в процессе: Производство';
        }

        // --- Документация ---
        if ($this->documentation) {
            if ($this->documentation->completed_at) {
                return 'Этап завершён: Документация';
            }
            return 'Этап в процессе: Документация';
        }

        // --- Договор ---
        if ($this->contract) {
            if ($this->contract->signed_at) {
                return 'Этап завершён: Договор';
            }
            return 'Этап в процессе: Договор';
        }

        // --- Замер ---
        if ($this->measurement) {
            if ($this->measurement->measured_at) {
                return 'Этап завершён: Замер';
            }
            if ($this->measurement->initial_meeting_at) {
                return 'Этап в процессе: Замер';
            }
            return 'Этап в процессе: Заявка';
        }

        // --- Встреча ---
        if ($this->meeting_at) {
            return 'Этап в процессе: Встреча';
        }

        return 'Этап в процессе: Не определён';
    }

    /**
     * Получить дату текущего незавершенного этапа
     */
    public function getCurrentStageDate()
    {
        // Определяем текущий этап и возвращаем соответствующую дату
        
        if (!$this->measurement || !$this->measurement->measured_at) {
            // Этап: measurement
            return $this->meeting_at ?? $this->measurement?->initial_meeting_at;
        }
        
        if (!$this->contract || !$this->contract->signed_at) {
            // Этап: contract  
            return $this->contract?->documentation_due_at ?? $this->measurement->measured_at;
        }
        
        if (!$this->documentation || !$this->documentation->completed_at) {
            // Этап: documentation
            return $this->contract->documentation_due_at ?? $this->contract->signed_at;
        }
        
        if (!$this->production || !$this->production->completed_at) {
            // Этап: production
            return $this->contract->ready_date ?? $this->documentation->completed_at;
        }
        
        if (!$this->installation || !$this->installation->installed_at) {
            // Этап: installation
            return $this->contract->installation_date ?? $this->production->completed_at;
        }
        
        // Все этапы завершены
        return $this->installation->installed_at;
    }

    /**
     * Scope для фильтрации заказов по текущему этапу
     * 
     * ШПАРГАЛКА ПО ЭТАПАМ:
     * 
     * 0. order         → все этапы      → СЛОЖНАЯ логика (заказы на любом этапе + завершенные)
     * 1. measurement   → measured_at    → Простая логика (только незавершенные)
     * 2. contract      → signed_at      → СЛОЖНАЯ логика (проверка последующих этапов)  
     * 3. documentation → completed_at   → Простая логика (только незавершенные)
     * 4. production    → completed_at   → Простая логика (только незавершенные)
     * 5. installation  → installed_at   → Простая логика (только незавершенные)
     * 
     * КАЛЕНДАРИ:
     * - Orders показывают полный pipeline заказов + завершенные (зеленые)
     * - Все простые этапы показывают только незавершенные задачи
     * - Контракты показывают незавершенные (строго) + завершенные (зеленые)
     * - Цвета: зеленый = завершено, красный/желтый/синий = по дедлайну
     */
    public function scopeInStage($query, $stage)
    {
        switch ($stage) {
            case 'measurement':
                // Просто незавершенный замер
                return $query->where(function($q) {
                    $q->whereDoesntHave('measurement')
                      ->orWhereHas('measurement', function($subQuery) {
                          $subQuery->whereNull('measured_at');
                      });
                });
                
            case 'contract':
                // СЛОЖНАЯ ЛОГИКА: замер готов, контракт не подписан, последующие этапы не завершены
                return $query->whereHas('measurement', function($q) {
                    $q->whereNotNull('measured_at');  // Замер завершен!
                })->where(function($q) {
                    $q->whereDoesntHave('contract')
                      ->orWhereHas('contract', function($subQuery) {
                          $subQuery->whereNull('signed_at');
                      });
                })->where(function($q) {
                    // Проверяем что последующие этапы НЕ завершены
                    $q->whereDoesntHave('documentation')
                      ->orWhereHas('documentation', function($subQuery) {
                          $subQuery->whereNull('completed_at');
                      });
                })->where(function($q) {
                    $q->whereDoesntHave('production')
                      ->orWhereHas('production', function($subQuery) {
                          $subQuery->whereNull('completed_at');
                      });
                })->where(function($q) {
                    $q->whereDoesntHave('installation')
                      ->orWhereHas('installation', function($subQuery) {
                          $subQuery->whereNull('installed_at');
                      });
                });
                
            case 'documentation':
                // Простая логика: только незавершенная документация
                return $query->where(function($q) {
                    $q->whereDoesntHave('documentation')
                      ->orWhereHas('documentation', function($subQuery) {
                          $subQuery->whereNull('completed_at');
                      });
                });
                
            case 'production':
                // Простая логика: только незавершенное производство
                return $query->where(function($q) {
                    $q->whereDoesntHave('production')
                      ->orWhereHas('production', function($subQuery) {
                          $subQuery->whereNull('completed_at');
                      });
                });
                
            case 'installation':
                // Простая логика: только незавершенная установка
                return $query->where(function($q) {
                    $q->whereDoesntHave('installation')
                      ->orWhereHas('installation', function($subQuery) {
                          $subQuery->whereNull('installed_at');
                      });
                });

            case 'order':
                // Показываем заказы на разных этапах + завершенные
                return $query->where(function($q) {
                    // Заказы на этапе замера (нет замера или не завершен)
                    $q->where(function($subQ) {
                        $subQ->whereDoesntHave('measurement')
                             ->orWhereHas('measurement', function($mq) {
                                 $mq->whereNull('measured_at');
                             });
                    })
                    // ИЛИ заказы на этапе контракта (замер готов, контракт не готов)
                    ->orWhere(function($subQ) {
                        $subQ->whereHas('measurement', function($mq) {
                            $mq->whereNotNull('measured_at');
                        })->where(function($cq) {
                            $cq->whereDoesntHave('contract')
                               ->orWhereHas('contract', function($subQuery) {
                                   $subQuery->whereNull('signed_at');
                               });
                        });
                    })
                    // ИЛИ другие этапы работ в процессе
                    ->orWhere(function($subQ) {
                        $subQ->whereHas('contract', function($cq) {
                            $cq->whereNotNull('signed_at');
                        })->where(function($workQ) {
                            $workQ->whereDoesntHave('documentation')
                                  ->orWhereHas('documentation', function($dq) {
                                      $dq->whereNull('completed_at');
                                  })
                                  ->orWhereDoesntHave('production')
                                  ->orWhereHas('production', function($pq) {
                                      $pq->whereNull('completed_at');
                                  })
                                  ->orWhereDoesntHave('installation')
                                  ->orWhereHas('installation', function($iq) {
                                      $iq->whereNull('installed_at');
                                  });
                        });
                    })
                    // ИЛИ полностью завершенные заказы (зеленые)
                    ->orWhere('status', 'completed');
                });
                
                
            default:
                return $query;
        }
    }
}
