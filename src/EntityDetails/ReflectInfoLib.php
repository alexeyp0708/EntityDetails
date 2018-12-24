<?php
	namespace Alpa\EntityDetails;
	trait ReflectInfoLib {

		private $buf_name_prop = [];
		protected $label_var = [
			'final' => '***',
			'private' => '**',
			'protected' => '*',
			'public' => '',
			'abstract' => '~',
			'static' => '%'
		];

		/**
		 *  Конвектор имени свойства обьекта.
		 *  @param string $name имя свойства обьекта которое надо конвертировать для данных в InfoObject или InfoClass
		 *  @param string $type тип конвертации  'construct' 'static' 'abstract' 'final' 'private' 'protected' 'public' 
		 */
		protected function nameConvert($name = false, $type = false) 
		{
			$bnp = &$this->buf_name_prop;
			switch ($type) {
				case false : $bnp = ['abstract' => '', 'static' => '', 'type' => '', 'name' => ''];
					break;
				case 'construct':
					$name = '__construct';
					$bnp['name'] = $name;
					break;
				case 'static':
				case 'abstract':
					$bnp[$type] = $this->label_var[$type];
				default:
					if (!empty($bnp['name'])) {
						$name = $bnp['name'];
					}
					$bnp['type'] = $this->label_var[$type];
					$name = trim($bnp['abstract'] . $bnp['static'] . $bnp['type'] . ' ' . $name);
					break;
			}
			return $name;
		}

	}