<?php


namespace App\Service\Section;


use App\Entity\Item;
use App\Entity\LastBotQuestion;
use App\Entity\Promocode;
use App\Dto\Promocode as PromocodeDto;
use DateTime;
use Exception;
use Telegram\Bot\Keyboard\Keyboard;

class Promocodes extends Base implements PromocodesInterface
{
    function start(?string $additional_text_to_header = null): void
    {
        $this->clearLastBotQuestion();
        $page = $this->getCallbackData()->p ?? 1;

        $limit = 5;

        $text = '🚀 Промокоды'.PHP_EOL.PHP_EOL.'Список всех промокодов:';

        if (!empty($additional_text_to_header)) {
            $text = $additional_text_to_header.PHP_EOL.PHP_EOL.$text;
        }

        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => '🖥 Создать промокод',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_PROMOCODES_CREATE
                ])
            ]);

        $promocodes = $this->promocodeRepository->getList($page, $limit);
        $pages = ceil($promocodes->count() / $limit);

        if ($promocodes->count() > 0) {

            /**
             * @var Promocode $promocode
             */
            foreach ($promocodes as $promocode) {
                $keyboard
                    ->row([
                        'text' => $promocode->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_PROMOCODES_INFO,
                            'id' => $promocode->getId()
                        ])
                    ]);
            }

            if ($pages > 1) {

                $previous_page = $page - 1;
                if ($previous_page < 1) {
                    $previous_page = $pages;
                }

                $next_page = $page + 1;
                if ($next_page > $pages) {
                    $next_page = 1;
                }

                $keyboard
                    ->row([
                        'text' => '◀️',
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_PROMOCODES,
                            'p' => $previous_page
                        ])
                    ], [
                        'text' => '▶️️',
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_PROMOCODES,
                            'p' => $next_page
                        ])
                    ]);
            }
        }

        $keyboard
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_MAIN_MENU
                ])
            ]);

        $this->sendMessage($text, $keyboard);
    }

    function create(): void
    {
        $this->getLastBotQuestion()->setType(LastBotQuestion::TYPE_PROMOCODES_ADD_PROMOCODE_NAME);
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());

        $text = '💬 Введите промокод:';
        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_PROMOCODES
                ])
            ]);

        $this->sendMessage($text, $keyboard);
    }

    function skipItem(): void
    {
        $this->getLastBotQuestion()
            ->addAnswer('item_id', null)
            ->setType(LastBotQuestion::TYPE_PROMOCODES_ADD_PROMOCODE_SELECT_TYPE)
        ;
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());
        $this->askSelectType();
    }

    function selectItem(): void
    {
        $id = $this->getCallbackData()->id;
        $this->getLastBotQuestion()
            ->addAnswer('item_id', $id)
            ->setType(LastBotQuestion::TYPE_PROMOCODES_ADD_PROMOCODE_SELECT_TYPE)
        ;
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());
        $this->askSelectType();
    }

    private function askSelectType(): void
    {
        $text = '💬 Выберите тип промокода:';
        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Одноразовый',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_PROMOCODES_CREATE_SELECT_TYPE,
                    't' => Promocode::TYPE_ONE_TIME
                ])
            ], [
                'text' => 'Многоразовый',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_PROMOCODES_CREATE_SELECT_TYPE,
                    't' => Promocode::TYPE_REF
                ])
            ])
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_BACK_TO_PREVIOUS_QUESTION,
                    'qt' => LastBotQuestion::TYPE_PROMOCODES_ADD_PROMOCODE_NAME
                ])
            ]);

        $this->sendMessage($text, $keyboard);
    }

    function selectType(): void
    {
        $type = $this->getCallbackData()->t;

        $this->getLastBotQuestion()
            ->addAnswer('type', $type)
            ->setType(LastBotQuestion::TYPE_PROMOCODES_ADD_PROMOCODE_DISCOUNT)
        ;
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());

        $text = '💬 Введите скидку (без %):';
        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_PROMOCODES_CREATE_SELECT_ITEM,
                    'id' => $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['item_id']
                ])
            ]);

        $this->sendMessage($text, $keyboard);
    }

    function handleUserAnswerOnAddPromocodeName(): void
    {
        $delete_user_answer = true;
        if ($this->isBackToPreviousQuestionCmd()) {
            $name = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['name'];
            $delete_user_answer = false;
        } else {
            $name = $this->getText();
        }

        if (empty($name)) {
            $text = '⚠️ Вы прислали что-то не то, введите текст:';
            $keyboard = (new Keyboard())
                ->inline()
                ->row([
                    'text' => 'Назад',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_PROMOCODES
                    ])
                ]);

            $this->sendMessage($text, $keyboard, $delete_user_answer);
        } else {

            $promocode = $this->promocodeRepository->findByName($name);

            if (!empty($promocode)) {
                $text = '⚠️ Такой промокод уже существует, придумайте другой:';
                $keyboard = (new Keyboard())
                    ->inline()
                    ->row([
                        'text' => 'Назад',
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_PROMOCODES
                        ])
                    ]);
                $this->sendMessage($text, $keyboard, $delete_user_answer);
            } else {
                $this->getLastBotQuestion()
                    ->addAnswer('name', $name)
                    ->setType(LastBotQuestion::TYPE_PROMOCODES_ADD_PROMOCODE_SELECT_ITEM);
                $this->lastBotQuestionRepository->save($this->getLastBotQuestion());
                $this->courses($delete_user_answer);
            }
        }
    }

    function handleUserAnswerOnAddPromocodeDiscount(): void
    {
        $discount = $this->getText();

        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_PROMOCODES_CREATE_SELECT_TYPE,
                    't' => $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['type']
                ])
            ]);

        if (empty($discount)) {
            $text = '⚠️ Вы прислали что-то не то, введите текст:';
        } else {
            if (preg_match('/^[1-9][0-9]?$|^100$/', $discount) === 0) {
                $text = '⚠️ Недопустимое значение, введите число в диапазон от 1 до 100';
            } else {
                $this->getLastBotQuestion()
                    ->addAnswer('discount', $discount)
                    ->setType(LastBotQuestion::TYPE_PROMOCODES_ADD_PROMOCODE_EXPIRE);
                $this->lastBotQuestionRepository->save($this->getLastBotQuestion());

                $text = '💬 Введите дату окончания (дд.мм.гггг):';
            }
        }

        $this->sendMessage($text, $keyboard, true);
    }

    function handleUserAnswerOnAddPromocodeExpire(): void
    {
        $expire = $this->getText();

        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_PROMOCODES_CREATE_SELECT_TYPE,
                    't' => $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['type']
                ])
            ]);

        if (empty($expire)) {
            $text = '⚠️ Вы прислали что-то не то, введите текст:';
            $this->sendMessage($text, $keyboard, true);
        } else {

            if (preg_match_all('/^\s*(3[01]|[12][0-9]|0?[1-9])\.(1[012]|0?[1-9])\.((?:19|20)\d{2})\s*$/', $expire) === 0) {
                $text = '⚠️ Дата должна соответствовать следующему формату: дд.мм.гггг';
                $this->sendMessage($text, $keyboard, true);
            } else {

                try {
                    $expireDateTime = new DateTime($expire);
                } catch (Exception $e) {
                    $this->logger->critical($e->getMessage());
                    die();
                }

                $name = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['name'];
                $item_id = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['item_id'];

                $item = $this->itemRepository->findById($item_id);

                $type = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['type'];
                $discount = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['discount'];

                $dto = new PromocodeDto($name, $item, $type, $discount, $expireDateTime);
                $promocode = Promocode::create($dto);
                $this->promocodeRepository->save($promocode);

                $this->info($promocode->getId());
            }
        }
    }

    function info(?int $id = null, ?string $additional_text_to_header = null): void
    {
        if (empty($id)) {
            $id = $this->getCallbackData()->id;
        }

        $promocode = $this->promocodeRepository->findById($id);

        $text = 'Промокод: '.$promocode->getName().PHP_EOL.PHP_EOL;

        if (!empty($promocode->getItem())) {
            $text .= 'Товар: '.$promocode->getItem()->getName().PHP_EOL.PHP_EOL;
        }

        if ($promocode->getType() === Promocode::TYPE_ONE_TIME) {
            $text .= 'Тип промокода: одноразовый'.PHP_EOL.PHP_EOL;
        } elseif ($promocode->getType() === Promocode::TYPE_REF) {
            $text .= 'Тип промокода: многоразовый'.PHP_EOL.PHP_EOL;
        }

        $text .= 'Количество переходов: '.$promocode->getTransitionsCount().PHP_EOL.PHP_EOL;
        $text .= 'Количество покупок: '.$promocode->getPurchasesCount().PHP_EOL.PHP_EOL;
        $text .= 'Скидка: '.$promocode->getDiscount().'%'.PHP_EOL.PHP_EOL;
        $text .= 'Дата окончания: '.$promocode->getExpire()->format('d.m.Y').PHP_EOL;

        if (!empty($additional_text_to_header)) {
            $text = $additional_text_to_header.PHP_EOL.PHP_EOL.$text;
        }

        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => '📝 Промокод',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_PROMOCODES_EDIT_NAME_QUESTION,
                    'id' => $promocode->getId()
                ])
            ], [
                'text' => '📝 Товар',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_PROMOCODES_EDIT_ITEM_QUESTION,
                    'id' => $promocode->getId()
                ])
            ])
            ->row([
                'text' => '📝 Дата',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_PROMOCODES_EDIT_EXPIRE_QUESTION,
                    'id' => $promocode->getId()
                ])
            ], [
                'text' => '📝 Скидка',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_PROMOCODES_EDIT_DISCOUNT_QUESTION,
                    'id' => $promocode->getId()
                ])
            ])
            ->row([
                'text' => '🗑 Удалить',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_PROMOCODES_REMOVE,
                    'id' => $promocode->getId()
                ])
            ])
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_PROMOCODES
                ])
            ]);

        $this->sendMessage($text, $keyboard);
    }

    function courses(bool $delete_user_answer = false): void
    {
        $page = $this->getCallbackData()->p ?? 1;

        $text = '💬 Выберите товар:';
        $keyboard = (new Keyboard())
            ->inline()
                ->row([
                    'text' => 'Пропустить',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_PROMOCODES_CREATE_SKIP_ITEM
                    ])
                ]);

        $limit = 5;
        $items = $this->itemRepository->getList($page, $limit);
        $pages = ceil($items->count() / $limit);

        if ($items->getIterator()->count() > 0) {
            /**
             * @var Item $item
             */
            foreach ($items as $item) {
                $keyboard
                    ->row([
                        'text' => '✅ ' . $item->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_PROMOCODES_CREATE_SELECT_ITEM,
                            'id' => $item->getId()
                        ])
                    ]);
            }
        }

        if ($pages > 1) {

            $previous_page = $page - 1;
            if ($previous_page < 1) {
                $previous_page = $pages;
            }

            $next_page = $page + 1;
            if ($next_page > $pages) {
                $next_page = 1;
            }

            $keyboard
                ->row([
                    'text' => '◀️',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_PROMOCODES_CREATE_ITEMS,
                        'p' => $previous_page
                    ])
                ], [
                    'text' => '▶️️',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_PROMOCODES_CREATE_ITEMS,
                        'p' => $next_page
                    ])
                ]);
        }

        $keyboard
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_PROMOCODES_CREATE
                ])
            ]);

        $this->sendMessage($text, $keyboard, $delete_user_answer);
    }

    function remove(): void
    {
        $id = $this->getCallbackData()->id;

        $promocode = $this->promocodeRepository->findById($id);

        $this->promocodeRepository->remove($promocode);

        $this->start('✅ Промокод успешно удален!');
    }

    function editNameQuestion(): void
    {
        $id = $this->getCallbackData()->id;

        $this->getLastBotQuestion()
            ->addAnswer('id', $id)
            ->setType(LastBotQuestion::TYPE_PROMOCODES_EDIT_PROMOCODE_NAME)
        ;
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());

        $text = '💬 Введите промокод:';
        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_PROMOCODES_INFO,
                    'id' => $id
                ])
            ]);

        $this->sendMessage($text, $keyboard);
    }

    function handleUserAnswerOnEditName(): void
    {
        $name = $this->getText();
        $id = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['id'];

        if (empty($name)) {
            $text = '⚠️ Вы прислали что-то не то, введите текст:';
            $keyboard = (new Keyboard())
                ->inline()
                ->row([
                    'text' => 'Назад',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_PROMOCODES_INFO,
                        'id' => $id
                    ])
                ]);

            $this->sendMessage($text, $keyboard, true);
        } else {
            $promocode = $this->promocodeRepository->findByName($name);

            if (!empty($promocode)) {
                $text = '⚠️ Такой промокод уже существует, придумайте другой:';
                $keyboard = (new Keyboard())
                    ->inline()
                    ->row([
                        'text' => 'Назад',
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_PROMOCODES_INFO,
                            'id' => $id
                        ])
                    ]);
                $this->sendMessage($text, $keyboard, true);
            } else {
                $promocode = $this->promocodeRepository->findById($id);
                $promocode->setName($name);
                $this->promocodeRepository->save($promocode);
                $this->info($id, '✅ Промокод успешно обновлен!');
            }
        }
    }

    function editItemQuestion(): void
    {
        $id = $this->getCallbackData()->id;
        $page = $this->getCallbackData()->p ?? 1;

        $this->getLastBotQuestion()
            ->addAnswer('id', $id)
            ->setType(LastBotQuestion::TYPE_PROMOCODES_EDIT_PROMOCODE_ITEM)
        ;
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());

        $text = '💬 Выберите товар:';
        $keyboard = (new Keyboard())->inline();

        $limit = 5;
        $items = $this->itemRepository->getList($page, $limit);
        $pages = ceil($items->count() / $limit);

        if ($items->getIterator()->count() > 0) {
            /**
             * @var Item $item
             */
            foreach ($items as $item) {
                $keyboard
                    ->row([
                        'text' => '✅ ' . $item->getName(),
                        'callback_data' => json_encode([
                            'c' => self::COMMAND_PROMOCODES_EDIT_ITEM,
                            'iid' => $item->getId()
                        ])
                    ]);
            }
        }

        if ($pages > 1) {

            $previous_page = $page - 1;
            if ($previous_page < 1) {
                $previous_page = $pages;
            }

            $next_page = $page + 1;
            if ($next_page > $pages) {
                $next_page = 1;
            }

            $keyboard
                ->row([
                    'text' => '◀️',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_PROMOCODES_EDIT_ITEM_QUESTION,
                        'id' => $id,
                        'p' => $previous_page
                    ])
                ], [
                    'text' => '▶️️',
                    'callback_data' => json_encode([
                        'c' => self::COMMAND_PROMOCODES_EDIT_ITEM_QUESTION,
                        'id' => $id,
                        'p' => $next_page
                    ])
                ]);
        }

        $keyboard
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_PROMOCODES_INFO,
                    'id' => $id
                ])
            ]);

        $this->sendMessage($text, $keyboard);
    }

    function editItem(): void
    {
        $id = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['id'];
        $item_id = $this->getCallbackData()->iid;

        $promocode = $this->promocodeRepository->findById($id);
        $item = $this->itemRepository->findById($item_id);

        $promocode->setItem($item);
        $this->promocodeRepository->save($promocode);

        $this->info($id, '✅ Промокод успешно обновлен!');
    }

    function editExpireQuestion(): void
    {
        $id = $this->getCallbackData()->id;

        $this->getLastBotQuestion()
            ->addAnswer('id', $id)
            ->setType(LastBotQuestion::TYPE_PROMOCODES_EDIT_PROMOCODE_EXPIRE)
        ;
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());

        $text = '💬 Введите дату окончания (дд.мм.гггг):';
        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_PROMOCODES_INFO,
                    'id' => $id
                ])
            ]);

        $this->sendMessage($text, $keyboard);
    }

    function handleUserAnswerOnEditExpire(): void
    {
        $expire = $this->getText();
        $id = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['id'];

        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_PROMOCODES_INFO,
                    'id' => $id
                ])
            ]);

        if (empty($expire)) {
            $text = '⚠️ Вы прислали что-то не то, введите текст:';
            $this->sendMessage($text, $keyboard, true);
        } else {

            if (preg_match_all('/^\s*(3[01]|[12][0-9]|0?[1-9])\.(1[012]|0?[1-9])\.((?:19|20)\d{2})\s*$/', $expire) === 0) {
                $text = '⚠️ Дата должна соответствовать следующему формату: дд.мм.гггг';
                $this->sendMessage($text, $keyboard, true);
            } else {

                try {
                    $expireDateTime = new DateTime($expire);
                } catch (Exception $e) {
                    $this->logger->critical($e->getMessage());
                    die();
                }

                $promocode = $this->promocodeRepository->findById($id);
                $promocode->setExpire($expireDateTime);
                $this->promocodeRepository->save($promocode);

                $this->info($id, '✅ Промокод успешно обновлен!');
            }
        }
    }

    function editDiscountQuestion(): void
    {
        $id = $this->getCallbackData()->id;

        $this->getLastBotQuestion()
            ->addAnswer('id', $id)
            ->setType(LastBotQuestion::TYPE_PROMOCODES_EDIT_PROMOCODE_DISCOUNT)
        ;
        $this->lastBotQuestionRepository->save($this->getLastBotQuestion());

        $text = '💬 Введите скидку (без %):';
        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_PROMOCODES_INFO,
                    'id' => $id
                ])
            ]);

        $this->sendMessage($text, $keyboard);
    }

    function handleUserAnswerOnEditDiscount(): void
    {
        $discount = $this->getText();
        $id = $this->getLastBotQuestion()->getAnswersFromPreviousQuestions()['id'];

        $keyboard = (new Keyboard())
            ->inline()
            ->row([
                'text' => 'Назад',
                'callback_data' => json_encode([
                    'c' => self::COMMAND_PROMOCODES_INFO,
                    'id' => $id
                ])
            ]);

        if (empty($discount)) {
            $text = '⚠️ Вы прислали что-то не то, введите текст:';
            $this->sendMessage($text, $keyboard, true);
        } else {
            if (preg_match('/^[1-9][0-9]?$|^100$/', $discount) === 0) {
                $text = '⚠️ Недопустимое значение, введите число в диапазон от 1 до 100';
                $this->sendMessage($text, $keyboard, true);
            } else {
                $promocode = $this->promocodeRepository->findById($id);
                $promocode->setDiscount($discount);
                $this->promocodeRepository->save($promocode);

                $this->info($id, '✅ Промокод успешно обновлен!');
            }
        }
    }
}