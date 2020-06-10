<?php

/**
 * @category   Entrepids
 * @package    Entrepids_CoffeeSolutions
 * @author     miguel.perez@entrepids.com
 * @coauthor   rmorales@mlg.com.mx
 * @website    http://www.entrepids.com
 */
class Entrepids_CoffeeSolutions_SolutionsController extends Mage_Core_Controller_Front_Action {

    /* validacion alterna a la url */
    public function valid2_url($valor=''){
        $url = null;

        if( isset( $_GET['SID'] ) ){
            $url = explode('/', $_GET['SID']);
        }

        $u = null;
        if( $url ){
            $l = false;
            $etr = 'SID';
            foreach ($url as $et => $r) {
                if( $l==false ){
                    $u[ $etr ] = $r;
                    $l = true;
                }else{
                    $etr = $r;
                    $l = false;
                }
            }
        }

        if( $valor!='' ){
            if( isset( $u[ $valor ] ) ){ $u = $u[ $valor ]; }
        }

        return $u;
    }
    /* procesa la seleccion de la categoria y proporciona las subcategorias */
    public function sublocationsAction(){

        $mainPlace = $this->getRequest()->getParam('place',null);
        if( $mainPlace==null ){
            $mainPlace = $this->valid2_url('place');
        }

        $places = array();
        $output = '';
        if(!empty($mainPlace)){
            $category = Mage::getModel('catalog/category')->load($mainPlace);
            if($category->getId()){
                $childsIds = $category->getChildren();
                $ids = explode(',', $childsIds);
                $subcategories = Mage::getModel('catalog/category')->getCollection()
                        ->addAttributeToSelect(array('entity_id', 'name', 'cs_solution_image', 'cs_solution_name','url_key'))
                        ->addFieldToFilter('entity_id', array('in' => $ids))
                        ->load();

                if (count($subcategories)) {
                    foreach ($subcategories as $p) {
                        if ($p->getCsSolutionName() && $p->getCsSolutionImage()) {
                            $imageResized = Mage::helper('coffeesolutions')->resizeImage($p->getCsSolutionImage(),'catalog/category',150,150);
                            $place = array('id' => $p->getId(), 'name' => $p->getCsSolutionName(), 'image' => $imageResized,'code' => $p->getUrlKey());
                            $places[] = $place;
                        }
                    }
                }
            }
        }
		if(!empty($places)){
            $output .= '<div class="subtitle-cs-places" id="coffee-solution-places-subtitle"><h4>Opciones para <span id="subtitle-innerplace">'.$category->getCsSolutionName().'</span></h4></div>';
            //$output .= '<h3>Opciones para <span>'.$category->getCsSolutionName().'</span></h3>';
            $output .= '<div class="innerplaces-container">';
            foreach ($places as $_p){
				$opcpara='opcpara_'.$nopc;
                $output .= '<div class="col-md-3 solution-place solution-sublocation" id="'.$_p['code'].'" onclick="setCoups('. $_p['id'].',\''.$_p['name'].'\',\''.$category->url_key.'\');" ';
                $output .= 'data-place="'.$category->url_key.'" data-sublocation="'.$_p['code'].'" data-cups="" >';
                $output .= '<img src="'.$_p['image'].'" title="'. $_p['name'].'"  class="solution-sublocation" data-place="'.$category->url_key.'" data-sublocation="'.$_p['code'].'" data-cups="">';
                $output .= '<label for="checkbox-'. $_p['id'].'" class="checkbox-label solution-sublocation" data-place="'.$category->url_key.'" data-sublocation="'.$_p['code'].'" data-cups="">';
                $output .= '<span data-place="'.$category->url_key.'" data-sublocation="'.$_p['code'].'" data-cups="" class="solution-sublocation"><span data-place="'.$category->url_key.'" data-sublocation="'.$_p['code'].'" data-cups="" class="solution-sublocation">'.$_p['name'].'</span></span></label>';
                $output .= '</div>';
            }
            $output .= '</div>';
        }else{
            $output .= '<p>No se encontraron resultados.</p>';
        }
        $output .= '<div class="clearfix"></div>';
        echo $output;
    }
    /* procesa la subcategoria y el numero de tazas para brindar una solucion */
    public function solutionsAction(){
        echo '<!-- action -->';

        $sublocation = (int)$this->getRequest()->getParam('sublocation',0);
        $numTazas = (int)$this->getRequest()->getParam('coups',0);

        if( $sublocation==null || $numTazas==null ){
            $sublocation = $this->valid2_url( 'sublocation' );
            $numTazas = $this->valid2_url( 'coups' );
        }

        $results = array();
        $output = '';
        if(!empty($sublocation)){
            $solutionsIds = array();
            $solutions = Mage::getModel('coffeesolutions/solutionPlaces')->getCollection();
            
            $solutions->getSelect()->join( 
                    array('solutions'=> 'entrepids_coffee_solutions'),
                    'solutions.entity_id = main_table.solution_id AND solutions.available = 1',
                    array('solution_name','solution_image','available','description','calification','costbycoup','preparation','beveragetype','varieties','min_coups_available','max_coups_available'));
            
            $solutions = $solutions->addFieldToFilter('category_id',array('eq'=>$sublocation))
                    ->addFieldToSelect('*')
                    ->setOrder('prioridad','ASC');
            
            $imagePath = $path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)  . 'solutions';
            if(!empty($solutions)){
                foreach ($solutions as $_s){
                    $maxCoups = (int)$_s->getMaxCoupsAvailable();
                    $minCoups = (int)$_s->getMinCoupsAvailable();
                    if($maxCoups && $maxCoups < $numTazas){
                        //Mage::log('La solucion '.$_s->getSolutionName().' no cumple con el máximo de tazas.  Comparando '.$maxCoups.' con '.$numTazas,null,'coups.log');
                        continue;
                    }
                    if($minCoups > $numTazas){
                        //Mage::log('La solucion '.$_s->getSolutionName().' no cumple con el mínimo de tazas. Comparando '.$minCoups.' con '.$numTazas,null,'coups.log');
                        continue;
                    }
                    $temp = array(
                        'id'=>$_s->getSolutionId(),
                        'name' => $_s->getSolutionName(), 
                        'description' => $_s->getDescription(),
                        'calification' => $_s->getCalification(),
                        'costbycoup' => $_s->getCostbycoup(),
                        'preparation' => $_s->getPreparation(),
                        'beveragetype' => $_s->getBeveragetype(),
                        'varieties' => $_s->getVarieties(),
                        'image' => $imagePath.$_s->getSolutionImage());
                    $results[] = $temp;
                }
            }
        }
        if(!empty($results)){
            foreach ($results as $_r){
                $output .= '<div class="col-md-3 solution-place solution-option" id="solution_'.$_r['id'].'" onclick="goToSolution('. $_r['id'].');" data-name="'.$_r['name'].'">';
                $output .= '<img src="'.$_r['image'].'" title="'.$_r['name'].'" class="solution-option" data-name="'.$_r['name'].'">';
                $output .= '<label for="checkbox-'. $_r['id'].'" class="checkbox-label solution-option" data-name="'.$_r['name'].'"><span>'. $_r['name'].'</span></label>';
                if(!empty($_r['calification'])){
                    $output .= '<label for="calificacion-'.$_r['id'].'" class="calificacion-label vote'.$_r['calification'].' solution-option" data-name="'.$_r['name'].'">Calificación: <span>'.$_r['calification'].'</span></label>';
                }
                if(!empty($_r['description'])){
                    $output .= '<p class="descripcion-label solution-option" data-name="'.$_r['name'].'">'.$_r['description'].'</p>';
                }
                if(!empty($_r['costbycoup'])){
                    $output .= '<label class="costbycoup-label solution-option" data-name="'.$_r['name'].'"><span>COSTO POR TAZA:</span> '.$_r['costbycoup'].'</label>';
                }
                if(!empty($_r['preparation'])){
                    $output .= '<label class="preparation-label solution-option" data-name="'.$_r['name'].'"><span>PREPARACIÓN:</span> '.$_r['preparation'].'</label>';
                }
                if(!empty($_r['beveragetype'])){
                    $output .= '<label class="beveragetype-label solution-option" data-name="'.$_r['name'].'"><span>TIPO DE BEBIDA:</span> '.$_r['beveragetype'].'</label>';
                }
                if(!empty($_r['varieties'])){
                    $output .= '<label class="varieties-label solution-option" data-name="'.$_r['name'].'"><span>VARIEDADES:</span> '.$_r['varieties'].'</label>';
                }
                $output .= '</div>';
            }
        }else{
            $output .= '<p>No se encontraron resultados.</p>';
        }
        $output .= '<div class="clearfix"></div>';
        echo $output;
    }
    
    public function detailAction(){
        //echo '<!-- detail -->';

        $id = (int)$this->getRequest()->getParam('id',null);
        $place = (int)$this->getRequest()->getParam('lugar',null);
        $sublocation = (int)$this->getRequest()->getParam('ubicacion',null);
        $coups = (int)$this->getRequest()->getParam('tazas',null);
        if($id && $place && $sublocation && $coups){
            $this->loadLayout();
            $this->getLayout()->getBlock('head')->setTitle($this->__('Soluciones de Café'));
            $this->renderLayout();
        }else{
            $this->_redirect("/");
        }   
    }
    
    public function add2cartAction(){
        //echo '<!-- add2cart -->';

        $products = $this->getRequest()->getParam('product');
        $qtys = $this->getRequest()->getParam('qty');
        $idSolucion = (int)$this->getRequest()->getParam('idsolucion');
        if(!empty($idSolucion) && is_array($products) && !empty($products) && is_array($qtys) && !empty($qtys)){
            $cart = Mage::helper('checkout/cart')->getCart();
            $products = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->getCollection()
                    ->addAttributeToSelect(array('name','price','final_price'))
                    ->addFieldToFilter('entity_id', array('in'=>$products));
            $products->getSelect()->join(array('c'=>'cataloginventory_stock_item'),'e.entity_id = c.item_id',array('c.qty'));

            foreach ($products as $_p){
                if(isset($qtys[$_p->getId()]) && $qtys[$_p->getId()] > 0){
                    //echo $_p->getName().' Qty: '.$qtys[$_p->getId()].'<br/>';
                    $params = array( //'product' => $_p->getId(),
                        'qty' => $qtys[$_p->getId()],
                        'form_key' => Mage::getSingleton('core/session')->getFormKey());
                    try{
                        if($qtys[$_p->getId()] <= $_p->getQty()){
                            $cart->addProduct($_p->getId(), $params);
                        }else{
                            Mage::getSingleton('customer/session')->addError('No se pudo agregar el producto '. $_p->getName() .'. no está disponible.'); 
                            Mage::getSingleton('customer/session')->addError('La cantidad solicitada de "'. $_p->getName() .'" no está disponible.'); 
                        }
                    }catch(Exception $e){
                        Mage::getSingleton('customer/session')->addError('No se pudo agregar el producto '. $_p->getName() .'. '.$e->getMessage()); 
                    }
                }
            }
            $cart->save();
            Mage::getSingleton('customer/session')->addSuccess('Se ha agregado tu solución de Café al carrito de compras.'); 
            $this->_redirect('checkout/cart');
        }else{
            Mage::getSingleton('customer/session')->addError('Debes seleccionar al menos un producto para poder agregar la solución a tu carrito.'); 
            $this->_redirectUrl(Mage::getSingleton('core/session')->getLastUrl());
        }
    }
    
    
}
