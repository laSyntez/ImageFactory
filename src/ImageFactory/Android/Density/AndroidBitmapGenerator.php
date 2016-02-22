<?php

namespace ImageFactory\Android\Density;

use ImageFactory\Image\ImageGeneratorInterface;
use ImageFactory\Exception\InvalidDriverException;
use ImageFactory\Exception\InvalidCompressionTypeException;
use ImageFactory\Android\Exception\InvalidBitmapTypeException;
use ImageFactory\Android\Exception\InvalidReferenceSizeException;

class AndroidBitmapGenerator implements ImageGeneratorInterface
{
    /**
     * @var \Imagine\Image\ImagineInterface
     */
	protected $imagine;
	
	/**
     * @var string
     */
	protected $imagePath;	
	
	/**
     * @var string
     */
	protected $outputPath;	
	
	/**
     * @var integer
     */
	protected $referenceWidth = self::REFERENCE_WIDTH_UNDEFINED;	
	
	/**
     * @var integer
     */
	protected $referenceHeight = self::REFERENCE_HEIGHT_UNDEFINED;
	
	/**
     * @var array
     */
	protected $densities = array();	
	
	/**
	 * @var string
	 */	
	protected $bitmapType = self::BITMAP_TYPE_REGULAR;

	/**
     * @var string
     */
	protected $compressionType = self::COMPRESSION_PNG;	
	
	/**
     * @var integer
     */
	protected $compressionValue = self::COMPRESSION_PNG_DEFAULT_LEVEL;
	
	
	const DENSITY_MDPI    = 'mdpi';
	const DENSITY_HPI     = 'hdpi';
	const DENSITY_XHDPI   = 'xhdpi';
	const DENSITY_XXHDPI  = 'xxhdpi';
	const DENSITY_XXXHDPI = 'xxxhdpi';
	
	const BITMAP_TYPE_REGULAR = 'REGULAR_BITMAP';
	const BITMAP_TYPE_ICON_LAUNCHER = 'ICON_LAUNCHER_BITMAP';
	
	const REFERENCE_WIDTH_MINIMUM = 4;
	const REFERENCE_HEIGHT_MINIMUM = 4;		
	const REFERENCE_WIDTH_UNDEFINED = 0;
	const REFERENCE_HEIGHT_UNDEFINED = 0;	
	
	
	/**
     * Constructor.
     *
     * @param string $imagePath     
     * @param string $outputPath    
     * @param integer $referenceWidth
     * @param integer $referenceHeight
     */
	public function __construct($imagePath, $outputPath = null, $referenceWidth = self::REFERENCE_WIDTH_UNDEFINED, $referenceHeight = self::REFERENCE_HEIGHT_UNDEFINED) 
	{	
		$this->setDriver(self::DRIVER_GD);	
		$this->setImagePath($imagePath); 
		$this->setOutputPath($outputPath); 
		$this->setReferenceSize($referenceWidth, $referenceHeight);	
	}
	
	/**
     * Set the compression type and value
     *
     * @param string $type
     * @param integer $value
     *
     * @throws InvalidCompressionTypeException
     *
     * @return AndroidBitmapGenerator
     */
	public function setCompression($type, $value)	
	{
		if (in_array($type, array(self::COMPRESSION_PNG, self::COMPRESSION_JPEG))) {
			$this->compressionType = $type;			
			$this->compressionValue = (int) $value;
		} else {
			throw new InvalidCompressionTypeException('A valid compression type must be defined');
		}
		
		return $this;
	}	
	
	/**
     * Set the image path
     *
     * @param string $path
     *
     * @return AndroidBitmapGenerator
     */
	public function setImagePath($path)	
	{
		if (is_string($path)) {
			$this->imagePath = $path;
		}
		
		$this->setReferenceSize(-1, -1);
		
		return $this;
	}	
	
	/**
	 * Set the image type 
	 *
	 * @param string $type 
	 * 
	 * @return AndroidBitmapGenerator
	 */
	public function setBitmapType($type)
	{
	    if (!in_array($type, array(self::BITMAP_TYPE_REGULAR, self::BITMAP_TYPE_ICON_LAUNCHER))) {
	        throw new InvalidBitmapTypeException(
	            'A valid bitmap type must be defined, either '.self::BITMAP_TYPE_REGULAR.' or '.self::BITMAP_TYPE_ICON_LAUNCHER
	        );
	    }
	    
        $this->bitmapType = $type;
        
        return $this;
	}
	 
	 
	
	/**
     * Set the output path used to name each density file 
     *
     * @param string $path
     *
     * @return AndroidBitmapGenerator
     */
	public function setOutputPath($path)	
	{
		if (is_string($path)) {
			$this->outputPath = $path;
		}
		
		return $this;
	}	
	
	/**
	 * Set the reference width 
	 *
	 * @param integer $width
	 *
	 * @return AndroidBitmapGenerator
	 */
	public function setReferenceWidth($width)
	{
	    $width = (int) $width;
	    
	    if (self::REFERENCE_WIDTH_UNDEFINED < $width && $width < self::REFERENCE_WIDTH_MINIMUM) {
	        throw new InvalidReferenceSizeException('The width must be greater than or equal to '.self::REFERENCE_WIDTH_MINIMUM);
	    }
	    
	    if (self::REFERENCE_WIDTH_MINIMUM <= $width) {
	        $this->referenceWidth = $width;
	    } else {
	        $this->referenceWidth = self::REFERENCE_WIDTH_UNDEFINED;
	    }
	    
	    return $this;
	}
	
	/**
	 * Set the reference height 
	 *
	 * @param integer $height
	 *
	 * @return AndroidBitmapGenerator
	 */
	public function setReferenceHeight($height)
	{
	    $height = (int) $height;
	    
	    if (self::REFERENCE_HEIGHT_UNDEFINED < $height && $height < self::REFERENCE_HEIGHT_MINIMUM) {
	        throw new InvalidReferenceSizeException('The height must be greater than or equal to '.self::REFERENCE_HEIGHT_MINIMUM);
	    }
	    
	    if (self::REFERENCE_HEIGHT_MINIMUM <= $height) {
	        $this->referenceHeight = $height;
	    } else {
	        $this->referenceHeight = self::REFERENCE_HEIGHT_UNDEFINED;
	    }
	    
	    return $this;
	}
	
	/**
     * Set the reference size necessary for defining the size of each density image
     *
     * @param integer $width
     * @param integer $height
     *
     * @return AndroidBitmapGenerator
     */
	public function setReferenceSize($width, $height)
	{
		$this->setReferenceWidth($width);
		$this->setReferenceHeight($height);
		
		return $this;
	}
	
	/**
     * Set the sizes of each density based on the reference size 
     */
	protected function setDensities()
	{
		$size = $this->imagine->open($this->imagePath)->getSize();
        $width = $size->getWidth();		
        $height = $size->getHeight();		
        $ratio = $width/$height;
        
	    if ($this->referenceWidth == self::REFERENCE_WIDTH_UNDEFINED && self::REFERENCE_HEIGHT_UNDEFINED == $this->referenceHeight) {
			$this->referenceWidth = $width;			
			$this->referenceHeight = $height;
		} elseif (self::REFERENCE_WIDTH_UNDEFINED == $this->referenceWidth) {
		    $this->referenceWidth = $this->referenceHeight*$ratio;
		} elseif (self::REFERENCE_HEIGHT_UNDEFINED == $this->referenceHeight) {
		    $this->referenceHeight = $this->referenceWidth/$ratio;
        }		
		
		$this->densities = array(
			self::DENSITY_MDPI => array($this->referenceWidth/4, $this->referenceHeight/4),
			self::DENSITY_HPI => array($this->referenceWidth*3/8, $this->referenceHeight*3/8),
			self::DENSITY_XHDPI => array($this->referenceWidth/2, $this->referenceHeight/2),
			self::DENSITY_XXHDPI => array($this->referenceWidth*3/4, $this->referenceHeight*3/4),
			self::DENSITY_XXXHDPI => array($this->referenceWidth, $this->referenceHeight)
		);
	}
	
	/**
	 * Set the driver used by the Imagine library to generate the bitmaps
	 *
	 * @param string $driver
	 *
	 * @return AndroidBitmapGenerator
	 */
	public function setDriver($driver)
	{
	    if (!in_array($driver, array(self::DRIVER_GD, self::DRIVER_IMAGICK))) {
	        throw new InvalidDriverException('A valid driver must be defined, either '.self::DRIVER_GD.' or '.self::DRIVER_IMAGICK);
	    }
	    
	    switch ($driver) {
	        case self::DRIVER_GD: 
	            $this->imagine = new \Imagine\Gd\Imagine();
	            break;
	        case self::DRIVER_IMAGICK: 
	            $this->imagine = new \Imagine\Imagick\Imagine();	
	            break;
	    }
	    
	    return $this;
	}
	
	/**
     * Generate the different densities files
     *
     * @param string $compressionType
     * @param integer $compressionValue
     */
	public function execute()
	{
		$outputPath = null != $this->outputPath ? $this->outputPath : $this->imagePath;
		$dotPos = stripos($outputPath, '.');
		$path = substr($outputPath, 0, $dotPos);
		$ext = substr($outputPath, $dotPos+1);
		
		$this->setDensities();
		
		$filter = $this->imagine instanceof \Imagine\Imagick\Imagine 
		    ? \Imagine\Image\ImageInterface::FILTER_POINT 
		    : \Imagine\Image\ImageInterface::FILTER_UNDEFINED;
		    
		foreach ($this->densities as $dens => $size) {
			$this->imagine->open($this->imagePath)
			     ->resize(new \Imagine\Image\Box($size[0], $size[1]), $filter)
			     ->save($path.'-'.$dens.'.'.$ext,  array($this->compressionType => $this->compressionValue));
		    
		    if (self::BITMAP_TYPE_REGULAR == $this->bitmapType && $dens == self::DENSITY_XXHDPI) { 
		        break; 
	        }
		}
	}
}

