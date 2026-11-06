<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "post".
 *
 * @property int $idpost
 * @property string $title
 * @property string $content
 * @property string $date
 * @property string $username
 * @property string $status
 * @property string|null $rejection_reason
 *
 * @property Account $username0
 */
class Post extends \yii\db\ActiveRecord
{
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_PUBLISHED = 'published';
    const STATUS_REJECTED = 'rejected';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'post';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'content', 'date', 'username'], 'required'],
            [['title', 'content'], 'string'],
            [['date'], 'safe'],
            [['username'], 'string', 'max' => 45],
            [['username'], 'exist', 'skipOnError' => true, 'targetClass' => Account::className(), 'targetAttribute' => ['username' => 'username']],
        ];
    }

    public function submitForReview()
    {
        if (!in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED], true)) {
            $this->addError('status', 'Hanya artikel draft atau ditolak yang dapat diajukan.');
            return false;
        }

        $this->status = self::STATUS_PENDING;
        $this->rejection_reason = null;

        return $this->save(false, ['status', 'rejection_reason']);
    }

    public function approve()
    {
        if ($this->status !== self::STATUS_PENDING) {
            $this->addError('status', 'Hanya artikel pending yang dapat disetujui.');
            return false;
        }

        $this->status = self::STATUS_PUBLISHED;
        $this->rejection_reason = null;

        return $this->save(false, ['status', 'rejection_reason']);
    }

    public function reject($reason)
    {
        if ($this->status !== self::STATUS_PENDING) {
            $this->addError('status', 'Hanya artikel pending yang dapat ditolak.');
            return false;
        }

        if (!is_string($reason) || trim($reason) === '') {
            $this->addError('rejection_reason', 'Alasan penolakan wajib diisi.');
            return false;
        }

        $this->rejection_reason = trim($reason);
        $this->status = self::STATUS_REJECTED;

        return $this->save(false, ['status', 'rejection_reason']);
    }

    public function getStatusLabel()
    {
        $labels = [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING => 'Menunggu review',
            self::STATUS_PUBLISHED => 'Published',
            self::STATUS_REJECTED => 'Ditolak',
        ];

        return isset($labels[$this->status]) ? $labels[$this->status] : $this->status;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'idpost' => 'Idpost',
            'title' => 'Title',
            'content' => 'Content',
            'date' => 'Date',
            'username' => 'Username',
            'status' => 'Status',
            'rejection_reason' => 'Alasan penolakan',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsername0()
    {
        return $this->hasOne(Account::className(), ['username' => 'username']);
    }
}
