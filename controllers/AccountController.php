<?php

namespace app\controllers;

use Yii;
use app\models\Account;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * AccountController implements the CRUD actions for Account model.
 */
class AccountController extends Controller
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
                        'actions' => ['index', 'create', 'update', 'delete', 'view'],
                        'allow' => true,
                        'roles' => ['Admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Account models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Account::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Account model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Account model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Account();
        $model->scenario = Account::SCENARIO_CREATE;

        if ($model->load(Yii::$app->request->post())) {
            $role = Yii::$app->authManager->getRole($model->role);
            if ($role === null) {
                $model->addError('role', 'Role yang dipilih tidak tersedia.');
            } elseif ($model->password === '') {
                $model->addError('password', 'Password wajib diisi.');
            } else {
                $model->password = Yii::$app->security->generatePasswordHash($model->password);
                if ($model->save()) {
                    Yii::$app->authManager->assign($role, $model->username);
                    return $this->redirect(['view', 'id' => $model->username]);
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Account model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = Account::SCENARIO_UPDATE;
        $currentPassword = $model->password;

        if ($model->load(Yii::$app->request->post())) {
            $role = Yii::$app->authManager->getRole($model->role);
            if ($role === null) {
                $model->addError('role', 'Role yang dipilih tidak tersedia.');
            } else {
                $model->password = $model->password === ''
                    ? $currentPassword
                    : Yii::$app->security->generatePasswordHash($model->password);

                if ($model->save()) {
                    Yii::$app->authManager->revokeAll($model->username);
                    Yii::$app->authManager->assign($role, $model->username);
                    return $this->redirect(['view', 'id' => $model->username]);
                }
            }
        } else {
            $model->password = '';
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Account model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Account model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Account the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Account::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
