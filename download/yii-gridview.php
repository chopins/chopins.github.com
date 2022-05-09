<?php
//因为电脑故障维修中
//以下代码是使用手机在Github在线编辑
//代码质量问题请谅解

use yii\grid\GridView;
use yii\base\Model
use yii\data\ActiveDataProvider;
class supplier extends Model {
     public function tableName(){
     return 'supplier';
     }
}
class FilterModel extends supplier{
    public function filter($param) {
         $query = supplier::find();
         $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
         //数据检查规则略
         $this->load($param);
        
         //以下逻辑应当加上判断是否有下列参数
         switch($this->idFilter){
           case '1':
            $query->andFilterWhere(['>','id', 10]);
           break;
           case '2':
            $query->andFilterWhere(['>=','id', 10]);
            break;
            case '3':
            $query->andFilterWhere(['<','id', 10]);
            break;

            case '4':
            $query->andFilterWhere(['<=','id', 10]);
            break;

         }
         
         $query->andFilterWhere(['t_status' => $this->t_status]);
        $query->andFilterWhere(['like', 'name', $this->name]);
              ->andFilterWhere(['like', 'code', $this->code]);
        return $dataProvider;
    }
}
class Test ｛
    public function csv($data){
       //直接输出输出Csv
       //也可写到文件
    header('Content-Description: File Transfer');
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    foreach($data as $row){
      echo "$row['id'], $row['name'], $row['code'], $row['t_status']\n";
    }
    }
   public function indexAction()
   {
      $filterModel = new FilterModel;
      $params = Yii::$app->request->get();
      $dataProvider = $filterModel->filter($params);
      if(isset($params['csv'])){
       return $this->csv($dataProvider);
      }
      return GridView::widget([
           'filterModel' => $filterModel,
           'dataProvider' => $dataProvider,
      'columns' => [
        [
            'class' => 'yii\grid\CheckboxColumn',
            ['attribute'=>'id',
            'filter'=> ['1'=>'>10', 
                    '2'=>'>=10', 
                    '3'=>'<10',
                    '4'=>'<=10',
                     ],
              'filterInputOptions'=>['name'=>'idFilter','id'=>'idFilter']
            ],
           ['attribute'=> 'name', 
           'filter'=> ''],
           ['attribute'=> 'code',
           'filter'=> ''],
            ['attribute'=>'t_status',
            'filter' =>['ok','hold'],
            ]
        ],
]);

   }
｝
