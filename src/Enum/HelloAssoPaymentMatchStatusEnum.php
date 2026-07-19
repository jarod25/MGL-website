<?php
namespace App\Enum;
enum HelloAssoPaymentMatchStatusEnum:string{case PENDING='pending';case MATCHED='matched';case UNMATCHED_PARTICIPANT='unmatched_participant';case UNKNOWN_TIER='unknown_tier';case IDENTITY_MISMATCH='identity_mismatch';case AMOUNT_MISMATCH='amount_mismatch';case PAYMENT_CONFLICT='payment_conflict';case IGNORED='ignored';case REFUNDED='refunded';case MULTIPLE_REGISTRATIONS='multiple_registrations';public static function unresolvedValues():array{return [self::PENDING->value,self::UNMATCHED_PARTICIPANT->value,self::UNKNOWN_TIER->value];}}
