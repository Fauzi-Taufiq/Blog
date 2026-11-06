<?php

namespace app\controllers;

use Yii;
use app\models\Post;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * PostController implements the CRUD actions for Post model.
 */
class PostController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
	'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'create', 'update', 'delete', 'view', 'submit'],
                        'allow' => true,
                        'roles' => ['Author', 'Admin'],
                    ],
                    [
                        'actions' => ['approve', 'reject'],
                        'allow' => true,
                        'roles' => ['Admin'],
                    ],
                ],
            ],

            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'submit' => ['POST'],
                    'approve' => ['POST'],
                    'reject' => ['GET', 'POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Post models.
     * @return mixed
     */
    public function actionIndex()
    {
        if ($this->isAdmin()) {
            $query = Post::find()->where(['status' => Post::STATUS_PENDING]);
        } else {
            $query = Post::find()->where([
                'username' => Yii::$app->user->identity->username,
            ]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query->orderBy('date DESC'),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Post model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $this->checkPostAccess($model);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Post model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Post();

        if ($model->load(Yii::$app->request->post())) {
            $model->date = date('Y-m-d H:i:s');
            $model->username = Yii::$app->user->identity->username;
            $model->status = Post::STATUS_DRAFT;
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->idpost]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Post model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $this->checkPostAccess($model);
        if ($model->username !== Yii::$app->user->identity->username
            || !in_array($model->status, [Post::STATUS_DRAFT, Post::STATUS_REJECTED], true)
        ) {
            throw new ForbiddenHttpException('Artikel ini tidak dapat diedit.');
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->date = date('Y-m-d H:i:s');
            $model->username = Yii::$app->user->identity->username;
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->idpost]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Post model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $this->checkPostAccess($model);
        if (!$this->isAdmin()
            && ($model->username !== Yii::$app->user->identity->username
                || !in_array($model->status, [Post::STATUS_DRAFT, Post::STATUS_REJECTED], true))
        ) {
            throw new ForbiddenHttpException('Artikel ini tidak dapat dihapus.');
        }
        $model->delete();

        return $this->redirect(['index']);
    }

    public function actionSubmit($id)
    {
        $model = $this->findModel($id);
        $this->checkPostAccess($model);
        if ($model->username !== Yii::$app->user->identity->username) {
            throw new ForbiddenHttpException('Anda hanya dapat mengajukan artikel milik sendiri.');
        }

        if ($model->submitForReview()) {
            Yii::$app->session->setFlash('success', 'Artikel berhasil diajukan untuk review.');
        } else {
            Yii::$app->session->setFlash('error', implode(' ', $model->getFirstErrors()));
        }

        return $this->redirect(['index']);
    }

    public function actionApprove($id)
    {
        $model = $this->findModel($id);
        if ($model->approve()) {
            Yii::$app->session->setFlash('success', 'Artikel berhasil dipublikasikan.');
        } else {
            Yii::$app->session->setFlash('error', implode(' ', $model->getFirstErrors()));
        }

        return $this->redirect(['index']);
    }

    public function actionReject($id)
    {
        $model = $this->findModel($id);
        if ($model->status !== Post::STATUS_PENDING) {
            throw new ForbiddenHttpException('Hanya artikel pending yang dapat ditolak.');
        }

        if (Yii::$app->request->isPost) {
            if ($model->reject(Yii::$app->request->post('rejection_reason'))) {
                Yii::$app->session->setFlash('success', 'Artikel berhasil ditolak.');
                return $this->redirect(['index']);
            }
        }

        return $this->render('reject', ['model' => $model]);
    }

    protected function isAdmin()
    {
        return Yii::$app->user->can('Admin');
    }

    protected function checkPostAccess(Post $model)
    {
        if (!$this->isAdmin() && $model->username !== Yii::$app->user->identity->username) {
            throw new ForbiddenHttpException('Anda tidak memiliki akses ke artikel ini.');
        }
    }

    /**
     * Finds the Post model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Post the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Post::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
