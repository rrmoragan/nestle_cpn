<?php

if( !defined( 'M_SEARCH_LIB' ) ){

	define('M_SEARCH_LIB','v0.1');

	class mSearch{

		public $data = null;

		public function bloqued_words(){
			$a = array(
				'select',
				'update(',
				'insert(',
				'delete(',
				'union',
				'=',
				'<',
				'>',
				'waitfor',
				'@',
				'\\',
				'name_const(',
				'CHAR(',
				' XOR ',
				' xor ',
				' OR ',
				' or ',
				' AND ',
				' and ',
				'unhex(',
				'sleep(',
				'sysdate(',
				'now(',
				'if(',
			);

			return $a;
		}

		public function filter_words( $t='' ){
			$a = $this->bloqued_words();

			if( $a==null ){ return ''; }
			if( $t!='' ){ $t = $t.'.'; }

			$s = '';
			foreach ($a as $et => $r) {
				if( $s!='' ){ $s = $s." OR "; }
				$s = $s.$t."query_text like '%$r%' ";
			}

			$s = " ( $s ) ";
			return $s;
		}

		public function cols(){
			$a = array(
				'query_id',
				'query_text',
				//'num_results',
				'popularity',
				'is_active',
				//'is_processed',
				"cast( updated_at as date ) as updated_at"
			);

			return $a;
		}

		public function search_cols(){
			$a = $this->cols();
			if( $a == '' ){ return ' * '; }

			$s = '';
			foreach ($a as $et => $r) {
				if( $s!='' ){ $s = $s.', '; }
				$s = $s." $r";
			}

			$s = " $s ";
			return $s;
		}

		public function slist( $order='ASC',$bloqued=true ){

			$not = ' not ';
			if( $bloqued==false ){ $not = ''; }
			$this->data = null;
			$filter = $this->filter_words('csq');
			if( $filter!='' ){
				$filter = " where $not $filter ";
			}
			$cols = $this->search_cols(  );
			if( $cols==null ){ $cols = ' * '; }

			$s = "SELECT $cols from catalogsearch_query as csq $filter and ( cast( updated_at as date ) >= '2019-08-01' and cast( updated_at as date ) <= '2019-08-29' ) order by updated_at ASC";
			$s = "SELECT $cols from catalogsearch_query as csq $filter order by updated_at $order";
			//echo "\n sql ==> $s";
			$a = query( $s );
			if( $a == null ){
				return 0;
			}

			$this->data = $a;
			return count( $this->data );
		}

		public function slist_count( $bloqued=true ){
			$this->data = null;

			$not = ' not ';
			if( $bloqued==false ){ $not = ''; }

			$filter = $this->filter_words('csq');
			if( $filter!='' ){
				$filter = " where $not $filter ";
			}

			$cols = ' count(*) as n ';

			$s = "SELECT $cols from catalogsearch_query as csq $filter ";
			//echo "\n sql ==> $s";
			$a = query( $s );
			if( $a == null ){
				return 0;
			}

			return $a[0]['n'];
		}

		public function slist_bloqued(){
			return $this->slist( 'ASC', false );
		}

	}
}

/*
$a = new mSearch();
$n = $a->slist_count();
$a->slist( 'DESC' );

echo "\n registros totales ==> $n \n";
echo print_table( $a->data );
echo "\n registros totales ==> $n \n";
*/
?>