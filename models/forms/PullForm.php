<?php
namespace quoma\pageeditor\models\forms;

use Yii;

/**
 * Description of PullForm
 *
 * @author martin
 */
class PullForm extends \yii\base\Model
{
    public $username;
    public $password;
    
    public function rules()
    {
        return [
            [['username','password'], 'required']
        ];
    }
    
    public function attributeLabels() 
    {
        return [
            'username' => Yii::t('app', 'Username'),
            'password' => Yii::t('app', 'Password')
        ];
    }
    
    public function attributeHints() 
    {
        return [
            'password' => Yii::t('app', 'Password will not be saved.')
        ];
    }
    
    /**
     * Devuelve la url con nombre de usuario y clave
     * @param string $url
     * @return string
     */
    public function getFullUrl($repo)
    {
        $fullUrl = str_replace('https://', "https://$this->username:$this->password@", $this->getUrl($repo));
        return $fullUrl;
    }
    
    public function getUrl($repo)
    {
        $url = $repo;
        
        preg_match('/https:.*@/i', $repo, $match);
        if(isset($match[0])){
            $url = 'https://'.str_replace($match[0],'',$repo);
        }
        
        return $url;
        
    }
    
    /**
     * Clona el repositorio
     * @param string $repo repo url
     * @param string $path basepath
     * @param string $folder directorio donde clonar: $path/$folder
     * @return type
     */
    public function cloneRepo($repo, $path, $folder)
    {
        $fullUrl = $this->getFullUrl($repo);
        $output = ['Clone from: '.$repo];
        
        if(file_exists($path.DIRECTORY_SEPARATOR.$folder)){
            $output[] = \yii\helpers\Html::tag('span',Yii::t('app',"Error: Folder $folder exists."),['class' => 'text-danger']);
            return [
                'status' => 'error',
                'output' => $output
            ];
        }
        
        set_time_limit(240);
        exec("cd $path && git clone $fullUrl $folder 2>&1", $output);
        
        $repoPath = $path.DIRECTORY_SEPARATOR.$folder;
        exec("cd $repoPath && git remote set-url origin {$repo} 2>&1", $output);
        
        if(file_exists($path.DIRECTORY_SEPARATOR.$folder)){
            return [
                'status' => 'success',
                'output' => $output
            ];
        }
        
        return [
            'status' => 'error',
            'output' => $output
        ];
        
    }
    
}
