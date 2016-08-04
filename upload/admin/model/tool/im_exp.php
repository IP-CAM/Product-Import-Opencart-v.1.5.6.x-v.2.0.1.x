<?php

class Modeltoolimexp extends Model
{
    function rus2translit($string)
    {
        $converter = array(
            'а' => 'a', 'б' => 'b', 'в' => 'v',
            'г' => 'g', 'д' => 'd', 'е' => 'e',
            'ё' => 'e', 'ж' => 'zh', 'з' => 'z',
            'и' => 'i', 'й' => 'y', 'к' => 'k',
            'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r',
            'с' => 's', 'т' => 't', 'у' => 'u',
            'ф' => 'f', 'х' => 'h', 'ц' => 'c',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
            'ь' => '\'', 'ы' => 'y', 'ъ' => '\'',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya',

            'А' => 'A', 'Б' => 'B', 'В' => 'V',
            'Г' => 'G', 'Д' => 'D', 'Е' => 'E',
            'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z',
            'И' => 'I', 'Й' => 'Y', 'К' => 'K',
            'Л' => 'L', 'М' => 'M', 'Н' => 'N',
            'О' => 'O', 'П' => 'P', 'Р' => 'R',
            'С' => 'S', 'Т' => 'T', 'У' => 'U',
            'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
            'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',
            'Ь' => '\'', 'Ы' => 'Y', 'Ъ' => '\'',
            'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
        );
        return strtr($string, $converter);
    }

    public function load_data_in_db()
    {
        $uploaddir = $_SERVER['DOCUMENT_ROOT'] . '/csv/';
        $uploadfile = $uploaddir . time() . basename($_FILES['csv']['name']);
        if (move_uploaded_file($_FILES['csv']['tmp_name'], $uploadfile)) {
            if (file_exists($uploadfile)) {
                $fo = fopen($uploadfile, 'r');
                if ($_POST['ready_tpl'] != 'none') {
                    $import_temp = $this->db->query("SELECT tpl FROM " . DB_PREFIX . "imports_tmpl WHERE id='" . $_POST['ready_tpl'] . "'")->row;
                    $import_temp = unserialize($import_temp['tpl']);
                } else {
                    $import_temp = $_POST;
                }
                while (($data = fgetcsv($fo, 0, ";")) !== FALSE) {
                    $query = $this->db->query("SELECT * FROM  " . DB_PREFIX . "product WHERE sku='" . addslashes($data[$import_temp['product_sku']]) . "'");
                    if (!$this->db->countAffected()) {
                        $data['product_model'] = (!empty($import_temp['product_model'])) ? $data[$import_temp['product_model']] : "";
                        $data['product_price'] = (!empty($import_temp['product_price'])) ? (float)$data[$import_temp['product_price']] : 0;
                        $data['product_description_name'] = (!empty($import_temp['product_description_name'])) ? addslashes($data[$import_temp['product_description_name']]) : "";
                        $data['product_description_description'] = (!empty($import_temp['product_description_description'])) ? addslashes($data[$import_temp['product_description_description']]) : "";
                        if (!empty($import_temp['product_to_category_category_id'])) {
                            if (!empty($data[$import_temp['product_to_category_category_id']])) {
                                $category_main_id = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "category_description WHERE name LIKE '%" . $data[$import_temp['product_to_category_category_id']] . "%'")->row;
                            }
                            $data['product_to_category_main_id'] = (!empty($category_main_id['category_id'])) ? $category_main_id['category_id'] : 0;
                        } else {
                            $data['product_to_category_main_id'] = 0;
                        }
                        if (!empty($import_temp['product_to_category_category_id_else'])) {
                            if (!empty($data[$import_temp['product_to_category_category_id_else']])) {
                                $data[$import_temp['product_to_category_category_id_else']] = str_replace(',', '|', $data[$import_temp['product_to_category_category_id_else']]);
                                $category_ids = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "category_description WHERE name REGEXP '" . $data[$import_temp['product_to_category_category_id_else']] . "'")->rows;
                            }
                            $data['product_to_category_category_id_else'] = (!empty($category_ids)) ? $category_ids : array();
                        } else {
                            $data['product_to_category_category_id_else'] = array();
                        }
                        $data['product_quantity'] = (!empty($import_temp['product_quantity'])) ? $data[$import_temp['product_quantity']] : 0;
                        if (!empty($import_temp['product_stock_status_id'])) {
                            $status = $this->db->query("SELECT stock_status_id FROM " . DB_PREFIX . "stock_status WHERE name LIKE '%" . $data[$import_temp['product_stock_status_id']] . "%'")->row;
                            $data['product_stock_status_id'] = (!empty($status['stock_status_id'])) ? $status['stock_status_id'] : 5;
                        } else {
                            $data['product_stock_status_id'] = 5;
                        }
                        $data['product_image'] = (!empty($import_temp['product_image'])) ? $data[$import_temp['product_image']] : "no_image.jpg";
                        if (!empty($import_temp['product_manufacturer_id'])) {
                            $manufacturer_id = $this->db->query("SELECT manufacturer_id FROM " . DB_PREFIX . "manufacturer WHERE name='" . $data[$import_temp['product_manufacturer_id']] . "';")->row;
                            $data['product_manufacturer_id'] = (!empty($manufacturer_id['manufacturer_id'])) ? $manufacturer_id['manufacturer_id'] : 0;
                        } else {
                            if (!empty($data[$import_temp['product_manufacturer_id']])) {
                                $this->db->query("INSERT INTO `manufacturer` (`name`) VALUES ('" . $data[$import_temp['product_manufacturer_id']] . "')");
                                $manufacturer_id = $this->db->query("SELECT manufacturer_id FROM " . DB_PREFIX . "manufacturer WHERE name='" . $data[$import_temp['product_manufacturer_id']] . "';")->row;
                                $data['product_manufacturer_id'] = (!empty($manufacturer_id['manufacturer_id'])) ? $manufacturer_id['manufacturer_id'] : 0;
                                $this->db->query("INSERT INTO `manufacturer_to_store` (`manufacturer_id`, `store_id`) VALUES (" . $data['product_manufacturer_id'] . ", '0');");
                            } else {
                                $data['product_manufacturer_id'] = 0;
                            }
                        }
                        $data['product_status'] = (!empty($import_temp['product_status']) && !empty($data[$import_temp['product_status']])) ? $data[$import_temp['product_status']] : 1;
                        if (!empty($import_temp['product_attribute'])) {
                            $data['product_attribute'] = explode(',', $data[$import_temp['product_attribute']]);
                            foreach ($data['product_attribute'] as $key => $value) {
                                $data['product_attribute'][$key] = explode('_', $value);
                            }
                        }
                        $data['product_description_meta_description'] = (!empty($import_temp['product_description_meta_description'])) ? $data[$import_temp['product_description_meta_description']] : "";
                        $data['product_description_meta_keyword'] = (!empty($import_temp['product_description_meta_keyword'])) ? $data[$import_temp['product_description_meta_keyword']] : "";
                        $data['product_description_seo_title'] = (!empty($import_temp['product_description_seo_title'])) ? $data[$import_temp['product_description_seo_title']] : "";
                        $data['product_description_seo_h1'] = (!empty($import_temp['product_description_seo_h1'])) ? $data[$import_temp['product_description_seo_h1']] : "";
                        $data['product_description_tag'] = (!empty($import_temp['product_description_seo_h1'])) ? $data[$import_temp['product_description_tag']] : "";
                        if (!empty($import_temp['product_image_id_image'])) {
                            $data['product_image_id_image'] = explode(',', $data[$import_temp['product_image_id_image']]);
                        }
                        $data['product_special_price'] = (!empty($import_temp['product_special_price'])) ? $data[$import_temp['product_special_price']] : 0;
                        $this->db->query("
							INSERT INTO `" . DB_PREFIX . "product` (
								`product_id`,
								`model`,
								`sku`, 
								`upc`, 
								`ean`, 
								`jan`, 
								`isbn`, 
								`mpn`, 
								`location`, 
								`quantity`, 
								`stock_status_id`, 
								`image`, 
								`manufacturer_id`, 
								`shipping`, 
								`price`, 
								`points`, 
								`tax_class_id`, 
								`date_available`, 
								`weight`, 
								`weight_class_id`, 
								`length`, 
								`width`, 
								`height`, 
								`length_class_id`, 
								`subtract`, 
								`minimum`, 
								`sort_order`, 
								`status`, 
								`date_added`, 
								`date_modified`, 
								`viewed`
							) 
							VALUES (
								null,
								'" . $data['product_model'] . "',
								'" . addslashes($data[$import_temp['product_sku']]) . "',
								'',
								'',
								'',
								'',
								'',
								'',
								'" . $data['product_quantity'] . "',
								" . $data['product_stock_status_id'] . ",
								'data/import/" . $data['product_image'] . "',
								'" . $data['product_manufacturer_id'] . "',
								1,
								'" . $data['product_price'] . "',
								0,
								0,
								now(),
								0,
								1,
								0,
								0,
								0,
								1,
								1,
								1,
								999,
								" . $data['product_status'] . ",
								now(),
								now(),
								0
							);
 						");
                        $last = $this->db->getLastId();
                        if (!empty($data['product_attribute'])) {
                            foreach ($data['product_attribute'] as $key => $value) {
                                $attr_id = $this->db->query("SELECT attribute_id FROM " . DB_PREFIX . "attribute_description WHERE name LIKE '%" . $value[0] . "%'")->row;
                                if (!empty($attr_id) && !empty($value[0]) && !empty($value[1])) {
                                    $this->db->query("INSERT INTO `" . DB_PREFIX . "product_attribute` (`product_id`, `attribute_id`, `language_id`, `text`) VALUES (" . $last . ", " . $attr_id["attribute_id"] . ", 1, '" . $value[1] . "');");
                                }
                            }
                        }
                        $this->db->query("INSERT INTO `" . DB_PREFIX . "product_description` (`product_id`, `language_id`, `name`, `description`, `meta_description`, `meta_keyword`, `tag`) VALUES (" . $last . ", 4, '" . $data['product_description_name'] . "', '" . $data['product_description_description'] . "', '" . $data['product_description_meta_description'] . "', '" . $data['product_description_meta_keyword'] . "', '" . $data['product_description_tag'] . "');");
                        if (!empty($data['product_to_category_main_id'])) {
                            $this->db->query("INSERT INTO `" . DB_PREFIX . "product_to_category` (`product_id`, `category_id`) VALUES (" . $last . ", " . $data['product_to_category_main_id'] . ");");
                        }
                        if (!empty($data['product_to_category_category_id_else'])) {
                            foreach ($data['product_to_category_category_id_else'] as $value) {
                                if ($value['category_id'] != $data['product_to_category_main_id']) {
                                    $this->db->query("INSERT INTO `" . DB_PREFIX . "product_to_category` (`product_id`, `category_id`, `main_category`) VALUES (" . $last . ", " . $value['category_id'] . ", 0);");
                                }
                            }
                        }
                        if (!empty($data['product_image_id_image'])) {
                            foreach ($data['product_image_id_image'] as $key => $value) {
                                $this->db->query("INSERT INTO `" . DB_PREFIX . "product_image` (`product_image_id`, `product_id`, `image`, `sort_order`) VALUES (null, " . $last . ", 'data/import/" . $value . "', 999);");
                            }
                        }
                        if (!empty($data['product_special_price'])) {
                            $this->db->query("INSERT INTO `" . DB_PREFIX . "product_special` (`product_special_id`, `product_id`, `customer_group_id`, `priority`, `price`, `date_start`, `date_end`) VALUES (null, " . $last . ", 1, 1, " . $data['product_special_price'] . ", '0000-00-00', '0000-00-00');");
                        }
                        $str = $this->rus2translit($data[$import_temp['product_description_name']]);
                        $str = strtolower($str);
                        $str = preg_replace('~[^-a-z0-9_]+~u', '-', $str);
                        $str = trim($str, "-");
                        $this->db->query("INSERT INTO `" . DB_PREFIX . "url_alias` (`url_alias_id`, `query`, `keyword`) VALUES (null, 'product_id=" . $last . "', '" . $str . "');");
                        $this->db->query("INSERT INTO `" . DB_PREFIX . "product_to_store` (`product_id`, `store_id`) VALUES (" . $last . ", 0);");

                    } else {
                        $data['product_model'] = (!empty($import_temp['product_model'])) ? $data[$import_temp['product_model']] : "";
                        $data['product_price'] = (!empty($import_temp['product_price'])) ? (float)$data[$import_temp['product_price']] : 0;
                        $data['product_description_name'] = (!empty($import_temp['product_description_name'])) ? addslashes($data[$import_temp['product_description_name']]) : "";
                        $data['product_description_description'] = (!empty($import_temp['product_description_description'])) ? addslashes($data[$import_temp['product_description_description']]) : "";
                        if (!empty($import_temp['product_to_category_category_id'])) {
                            if (!empty($data[$import_temp['product_to_category_category_id']])) {
                                $category_main_id = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "category_description WHERE name LIKE '%" . $data[$import_temp['product_to_category_category_id']] . "%'")->row;
                            }
                            $data['product_to_category_main_id'] = (!empty($category_main_id['category_id'])) ? $category_main_id['category_id'] : 0;
                        } else {
                            $data['product_to_category_main_id'] = 0;
                        }
                        if (!empty($import_temp['product_to_category_category_id_else'])) {
                            if (!empty($data[$import_temp['product_to_category_category_id_else']])) {
                                $data[$import_temp['product_to_category_category_id_else']] = str_replace(',', '|', $data[$import_temp['product_to_category_category_id_else']]);
                                $category_ids = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "category_description WHERE name REGEXP '" . $data[$import_temp['product_to_category_category_id_else']] . "'")->rows;
                            }
                            $data['product_to_category_category_id_else'] = (!empty($category_ids)) ? $category_ids : array();
                        } else {
                            $data['product_to_category_category_id_else'] = array();
                        }
                        $data['product_quantity'] = (!empty($import_temp['product_quantity'])) ? $data[$import_temp['product_quantity']] : 0;
                        if (!empty($import_temp['product_stock_status_id'])) {
                            $status = $this->db->query("SELECT stock_status_id FROM " . DB_PREFIX . "stock_status WHERE name LIKE '%" . $data[$import_temp['product_stock_status_id']] . "%'")->row;
                            $data['product_stock_status_id'] = (!empty($status['stock_status_id'])) ? $status['stock_status_id'] : 5;
                        } else {
                            $data['product_stock_status_id'] = 5;
                        }
                        $data['product_image'] = (!empty($import_temp['product_image'])) ? $data[$import_temp['product_image']] : "no_image.jpg";
                        if (!empty($import_temp['product_manufacturer_id'])) {
                            $manufacturer_id = $this->db->query("SELECT manufacturer_id FROM " . DB_PREFIX . "manufacturer WHERE name='" . $data[$import_temp['product_manufacturer_id']] . "';")->row;
                            $data['product_manufacturer_id'] = (!empty($manufacturer_id['manufacturer_id'])) ? $manufacturer_id['manufacturer_id'] : 0;
                        } else {
                            $data['product_manufacturer_id'] = 0;
                        }
                        $data['product_status'] = (!empty($import_temp['product_status'])) ? $data[$import_temp['product_status']] : 1;
                        if (!empty($import_temp['product_attribute'])) {
                            $data['product_attribute'] = explode(',', $data[$import_temp['product_attribute']]);
                            foreach ($data['product_attribute'] as $key => $value) {
                                $data['product_attribute'][$key] = explode('_', $value);
                            }
                        }
                        $data['product_description_meta_description'] = (!empty($import_temp['product_description_meta_description'])) ? $data[$import_temp['product_description_meta_description']] : "";
                        $data['product_description_meta_keyword'] = (!empty($import_temp['product_description_meta_keyword'])) ? $data[$import_temp['product_description_meta_keyword']] : "";
                        $data['product_description_seo_title'] = (!empty($import_temp['product_description_seo_title'])) ? $data[$import_temp['product_description_seo_title']] : "";
                        $data['product_description_seo_h1'] = (!empty($import_temp['product_description_seo_h1'])) ? $data[$import_temp['product_description_seo_h1']] : "";
                        $data['product_description_tag'] = (!empty($import_temp['product_description_seo_h1'])) ? $data[$import_temp['product_description_tag']] : "";
                        if (!empty($import_temp['product_image_id_image'])) {
                            $data['product_image_id_image'] = explode(',', $data[$import_temp['product_image_id_image']]);
                        }
                        $data['product_special_price'] = (!empty($import_temp['product_special_price'])) ? $data[$import_temp['product_special_price']] : 0;

                        $product_id = $query->row['product_id'];

                        if (!empty($data['product_model'])) {
                            $this->db->query("UPDATE " . DB_PREFIX . "product SET model ='" . $data['product_model'] . "' WHERE product_id='" . $product_id . "'");
                        }
                        if (!empty($data['product_price'])) {
                            $this->db->query("UPDATE " . DB_PREFIX . "product SET price =" . $data['product_price'] . " WHERE product_id='" . $product_id . "'");
                        }
                        if (!empty($data['product_description_name'])) {
                            $this->db->query("UPDATE " . DB_PREFIX . "product_description SET name ='" . $data['product_description_name'] . "' WHERE product_id='" . $product_id . "'");
                        }
                        if (!empty($data['product_description_description'])) {
                            $this->db->query("UPDATE " . DB_PREFIX . "product_description SET description ='" . $data['product_description_description'] . "' WHERE product_id='" . $product_id . "'");
                        }
                        /*if(!empty($data['product_to_category_main_id'])){

                        }
                        if(!empty($data['product_to_category_category_id_else'])){

                        }*/

                        if (!empty($data['product_quantity'])) {
                            $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity =" . $data['product_quantity'] . " WHERE product_id='" . $product_id . "'");
                        }
                        if (!empty($data['product_stock_status_id']) && $data['product_stock_status_id'] != 5) {
                            $this->db->query("UPDATE " . DB_PREFIX . "product SET stock_status_id =" . $data['product_stock_status_id'] . " WHERE product_id='" . $product_id . "'");
                        }
                        if (!empty($data['product_image'])) {
                            $this->db->query("UPDATE " . DB_PREFIX . "product SET image ='" . $data['product_image'] . "' WHERE product_id='" . $product_id . "'");
                        }
                        if (!empty($data['product_manufacturer_id'])) {
                            $this->db->query("UPDATE " . DB_PREFIX . "product SET manufacturer_id =" . $data['product_manufacturer_id'] . " WHERE product_id='" . $product_id . "'");
                        }
                        if (!empty($import_temp['product_status'])) {
                            $this->db->query("UPDATE " . DB_PREFIX . "product SET status =" . $data['product_status'] . " WHERE product_id='" . $product_id . "'");
                        }
                        /*if(!empty($data['product_attribute'])){

                        }*/
                        if (!empty($data['product_description_meta_description'])) {
                            $this->db->query("UPDATE " . DB_PREFIX . "product_description SET meta_description ='" . $data['product_description_meta_description'] . "' WHERE product_id='" . $product_id . "'");
                        }
                        if (!empty($data['product_description_meta_keyword'])) {
                            $this->db->query("UPDATE " . DB_PREFIX . "product_description SET meta_keyword ='" . $data['product_description_meta_keyword'] . "' WHERE product_id='" . $product_id . "'");
                        }
                        if (!empty($data['product_description_seo_title'])) {
                            $this->db->query("UPDATE " . DB_PREFIX . "product_description SET seo_title ='" . $data['product_description_seo_title'] . "' WHERE product_id='" . $product_id . "'");
                        }
                        if (!empty($data['product_description_seo_h1'])) {
                            $this->db->query("UPDATE " . DB_PREFIX . "product_description SET seo_h1 ='" . $data['product_description_seo_h1'] . "' WHERE product_id='" . $product_id . "'");
                        }
                        if (!empty($data['product_description_tag'])) {
                            $this->db->query("UPDATE " . DB_PREFIX . "product_description SET tag ='" . $data['product_description_tag'] . "' WHERE product_id='" . $product_id . "'");
                        }
                        /*if(!empty($data['product_image_id_image'])){

                        }*/
                        if (!empty($data['product_special_price'])) {
                            $this->db->query("UPDATE " . DB_PREFIX . "product_special SET price =" . $data['product_special_price'] . " WHERE product_id='" . $product_id . "'");
                        }


                        /*echo "test";
                        if($data[11]=="есть в наличии"){$quality=1;} else{$quality=0;}
                        $id=$this->db->query("SELECT product_id FROM product WHERE sku='".$data[3]."'")->row["product_id"];
                        $this->db->query("UPDATE product SET quantity=".$quality.", price=".$data[14]." WHERE sku='".$data[3]."'");
                        if(!empty($data[13])){
                            $this->db->query("REPLACE INTO `product_special` (`product_special_id`, `product_id`, `customer_group_id`, `priority`, `price`, `date_start`, `date_end`) VALUES (null, ".$id.", 1, 1, ".$data[13].", '0000-00-00', '0000-00-00');");
                        }
                        else{
                            $this->db->query("DELETE FROM `product_special` WHERE  `product_id`=".$id.";");
                        }*/
                    }
                }
            }

            //$csv=ob_get_contents();
            //ob_end_clean();

            /*$row=preg_split("/\n/",$csv);
            array_pop($row);
            foreach ($row as $ind => $val)
            {
                $row[$ind]=explode(";",$row[$ind]);
            }
            foreach ($row as $line)
            {
                $this->db->query("UPDATE product SET quantity='".$line[1]."', price='".$line[2]."',purchase_price='".$line[3]."',`status`='".$line[4]."' WHERE model='".$line[0]."'");
            }*/
            //unlink($uploadfile);
            return true;
        } else {
            return false;
        }
    }

    public function upload_data_from_db()
    {
        $res = $this->db->query("SELECT product_description.name as pn ,GROUP_CONCAT(DISTINCT category_description.name SEPARATOR '#') AS cat, product.sku, manufacturer.name as mn, product.model, GROUP_CONCAT(product_attribute.text SEPARATOR '#') as attr, product.quantity as qu, product_special.price as ap, product.price, url_alias.keyword, product_description.seo_h1, product_description.seo_title, product_description.meta_keyword, product_description.meta_description, product_description.description, product_description.tag, GROUP_CONCAT(DISTINCT product_image.image) as img FROM product LEFT JOIN product_attribute ON (product.product_id = product_attribute.product_id)LEFT JOIN product_description ON (product.product_id = product_description.product_id) LEFT JOIN product_image ON (product.product_id = product_image.product_id) LEFT JOIN product_special ON (product.product_id = product_special.product_id) LEFT JOIN product_to_category ON (product.product_id = product_to_category.product_id) LEFT JOIN category_description ON(category_description.category_id = product_to_category.category_id) LEFT JOIN manufacturer ON (manufacturer.manufacturer_id = product.manufacturer_id) LEFT JOIN attribute_description ON(attribute_description.attribute_id = product_attribute.attribute_id) LEFT JOIN url_alias ON (url_alias.`query` = CONCAT('product_id=', product.product_id)) where product.product_id=78 GROUP BY product_image.product_id");
        foreach ($res->rows as $val) {
            $prereturn = array();
            foreach ($val as $key => $value) {
                if ($key == "cat") {
                    $ar = preg_split("/#/", $value);
                    $prereturn = array_merge($prereturn, $ar);
                } else if ($key == "attr") {
                    $ara = preg_split("/#/", $value);
                    $prereturn[] = $ara[0];
                    $prereturn[] = $ara[10];
                    $prereturn[] = $ara[20];
                    $prereturn[] = $ara[30];
                    $prereturn[] = $ara[40];
                    $prereturn[] = $ara[50];
                } else if ($key == "qu") {
                    if ($value > 0) {
                        $prereturn[] = "есть в наличии";
                    } else {
                        $prereturn[] = "нет в наличии";
                    }
                } else {
                    $prereturn[] = $value;
                }
            }
            $return[] = $prereturn;
        }
        return $return;
    }

    public function saveorder($name)
    {
        $data = serialize($_POST);
        // $name = "";
        // foreach ($_POST as $key => $value) {
        // 	$name .= $key.",";
        // }
        // $name = substr($name,0,70);
        // $name = $name."...";
        if (empty($name)) {
            $name = "Шаблон от " . date('d-m-Y', time());
        }
        $this->db->query("INSERT INTO `" . DB_PREFIX . "imports_tmpl` (`name`, `tpl`) VALUES ('" . $name . "', '" . $data . "');");
        return true;
    }

    public function getorder()
    {
        return $this->db->query("SELECT * FROM imports_tmpl")->rows;
    }

    public function createDB()
    {
        $this->db->query("
			CREATE TABLE `" . DB_PREFIX . "imports_tmpl` (	
				`id` INT(11) NOT NULL AUTO_INCREMENT,	
				`name` VARCHAR(255) NULL DEFAULT NULL,	
				`tpl` TEXT NOT NULL,	
				PRIMARY KEY (`id`) 
			)"
        );
    }

    public function deleteDB()
    {
        $this->db->query("DROP TABLE `" . DB_PREFIX . "imports_tmpl`");
    }

    public function delorder()
    {
        if (!empty($_POST['ready_tpl']) && $_POST['ready_tpl'] != 'none') {
            $this->db->query("DELETE FROM `" . DB_PREFIX . "imports_tmpl` WHERE `id`=" . $_POST['ready_tpl']);
            return true;
        } else {
            return false;
        }
    }
}

?>