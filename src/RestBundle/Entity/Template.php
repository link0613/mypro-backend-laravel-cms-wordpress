<?php

namespace RestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * @Vich\Uploadable
 * @ORM\Table(name="templates")
 * @ORM\Entity(repositoryClass="RestBundle\Entity\Repository\TestimonialRepository")
 * @Serializer\ExclusionPolicy("all")
 */
class Template
{
    /**
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Expose()
     * @Serializer\Groups({"profile"})
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @Serializer\Expose()
     * @Serializer\Groups({"profile"})
     */
    protected $name;

    /**
     * @Assert\File(maxSize="10M", mimeTypes={
     *     "application/pdf",
     *     "application/msword",
     *     "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
     *     "application/rtf",
     *     "text/rtf",
     *     "text/plain"
     * }, mimeTypesMessage="Please add a valid document")
     * @Vich\UploadableField(mapping="template", fileNameProperty="template_name")
     * @var File $template
     * @Serializer\Expose()
     * @Serializer\Groups({"profile"})
     */
    protected $template;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $template_name;

    /**
     * @Assert\Image(maxSize="2M", mimeTypes={"image/png", "image/jpeg"}, mimeTypesMessage="Please add a valid image")
     * @Vich\UploadableField(mapping="template_preview", fileNameProperty="preview_name")
     * @var File $template
     * @Serializer\Expose()
     * @Serializer\Groups({"profile"})
     */
    protected $preview;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $preview_name;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param mixed $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return mixed
     */
    public function getTemplateName()
    {
        return $this->template_name;
    }

    /**
     * @param mixed $template_name
     */
    public function setTemplateName($template_name)
    {
        $this->template_name = $template_name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return File
     */
    public function getPreview()
    {
        return $this->preview;
    }

    /**
     * @param File $preview
     */
    public function setPreview($preview)
    {
        $this->preview = $preview;
    }

    /**
     * @return mixed
     */
    public function getPreviewName()
    {
        return $this->preview_name;
    }

    /**
     * @param mixed $preview_name
     */
    public function setPreviewName($preview_name)
    {
        $this->preview_name = $preview_name;
    }
}