<?php

namespace ImageFactory\Android\Density;

use ImageFactory\Image\ImageGeneratorInterface;
use ImageFactory\Exception\InvalidCompressionTypeException;

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
	protected $referenceWidth;	
	
	/**
     * @var integer
     */
	protected $referenceHeight;
	
	/**
     * @var array
     */
	protected $densities = array();	
	
	/**
     * @var string
     */
	protected $compressionType = self::COMPRESSION_PNG;	
	
	/**
     * @var integer
     */
	protected $compressionValue = self::COMPRESSION_PNG_DEFAULT_LEVEL;
	
	/**
	 * @var string
	 */	
	protected $bitmapType = self::BITMAP_TYPE_REGULAR;
	
	const DENSITY_MDPI    = 'mdpi';
	const DENSITY_HPI     = 'hdpi';
	const DENSITY_XHDPI   = 'xhdpi';
	const DENSITY_XXHDPI  = 'xxhdpi';
	const DENSITY_XXXHDPI = 'xxxhdpi';
	
	const BITMAP_TYPE_ICON_LAUNCHER = 'ICON_LAUNCHER_BITMAP';
	const BITMAP_TYPE_REGULAR = 'REGULAR_BITMAP';
	
	/**
     * Constructor.
     *
     * @param string $imagePath     
     * @param string $outputPath    
     * @param integer $referenceWidth
     * @param integer $referenceHeight
     */
	public function __construct($imagePath, $outputPath = null, $referenceWidth = -1, $referenceHeight = -1) 
	{
		$this->imagine = new \Imagine\Imagick\Imagine();	
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
			$this-> compressionType = $type;			
			$this-> compressionValue = (int) $value;
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
     * Set output path used to name each density file 
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
     * Set the reference size necessary for defining the size of each density image
     *
     * @param integer $width
     * @param integer $height
     *
     * @return AndroidBitmapGenerator
     */
	public function setReferenceSize($width, $height)
	{
		if (4 < (int) $width && (int) $height > 4) {
			$this->referenceWidth = $width;
			$this->referenceHeight = $height;
		} else {
			$size = $this->imagine->open($this->imagePath)->getSize();
			$this->referenceWidth = $size->getWidth();			
			$this->referenceHeight = $size->getHeight();
		}	
		
		$this->setDensities();
		
		return $this;
	}
	
	/**
     * Set the sizes of each density based on the reference size 
     */
	protected function setDensities()
	{
		$this->densities = array(
			self::DENSITY_MDPI => array($this->referenceWidth/4, $this->referenceHeight/4),
			self::DENSITY_HPI => array($this->referenceWidth*3/8, $this->referenceHeight*3/8),
			self::DENSITY_XHDPI => array($this->referenceWidth/2, $this->referenceHeight/2),
			self::DENSITY_XXHDPI => array($this->referenceWidth*3/4, $this->referenceHeight*3/4),
			self::DENSITY_XXXHDPI => array($this->referenceWidth, $this->referenceHeight)
		);
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
		
		foreach ($this->densities as $dens => $size) {
			$this->imagine->open($this->imagePath)
			     ->resize(new \Imagine\Image\Box($size[0], $size[1]), \Imagine\Image\ImageInterface::FILTER_POINT)
			     ->save($path.'-'.$dens.'.'.$ext,  array($this->compressionType => $this->compressionValue));
		    
		    if (self::BITMAP_TYPE_REGULAR == $this->bitmapType && $dens == self::DENSITY_XXHDPI) { 
		        break; 
	        }
		}
	}
}

