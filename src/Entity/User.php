<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups("main")
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     * @Groups("main")
     */
    private $roles = [];

    /**
     * @ORM\Column(type="string", length=128)
     * @Groups("main")
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity=ApiToken::class, mappedBy="user", orphanRemoval=true)
     */
    private $apiTokens;

    /**
     * @ORM\OneToMany(targetEntity=Log::class, mappedBy="user")
     */
    private $logs;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $nickname;

    /**
     * @ORM\Column(type="string", length=24, nullable=true)
     */
    private $user_ip;

    /**
     * @ORM\Column(type="integer")
     */
    private $gdpr = 0;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity=FormAnswers::class, mappedBy="User")
     */
    private $formAnswers;

    /**
     * @ORM\ManyToOne(targetEntity=Countries::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $passwordRepeat;

    /**
     * @ORM\OneToMany(targetEntity=News::class, mappedBy="author")
     */
    private $news;

    /**
     * @ORM\Column(type="string", length=11)
     */
    private $uniqueId;

    /**
     * @ORM\Column(type="date")
     */
    private $birthdate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $coins = 0;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $scoresaberId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $scoresaberLink;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $twitchLink;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $discordNickname;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $donate = 0;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $loggedAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $imgName;

    /**
     * @ORM\ManyToMany(targetEntity=UserBadges::class, inversedBy="users")
     */
    private $badge;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $twitchNickname;

    /**
     * @ORM\ManyToOne(targetEntity=Hdm::class, inversedBy="users")
     */
    private $hdm;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $color = "#ffffff";

    /**
     * @ORM\ManyToMany(targetEntity=Tournaments::class, mappedBy="players")
     */
    private $tournaments;

    /**
     * @ORM\Column(type="smallint")
     */
    private $active = 1;

    /**
     * @ORM\OneToMany(targetEntity=Leaderboard::class, mappedBy="User")
     */
    private $leaderboards;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $gender;

    /**
     * @ORM\OneToMany(targetEntity=TournamentsScores::class, mappedBy="User")
     */
    private $tournamentsScores;

    /**
     * @ORM\Column(type="float", length=64, nullable=true)
     */
    private $avgPercentage = 0;

    /**
     * @param float $avgPercentage
     */
    public function setAvgPercentage(float $avgPercentage): void {
        $this->avgPercentage = $avgPercentage;
    }

    /**
     * @return float
     */
    public function getAvgPercentage(): float {
        return $this->avgPercentage;
    }

    public function __construct()
    {
        $this->apiTokens = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->formAnswers = new ArrayCollection();
        $this->news = new ArrayCollection();
        $this->badge = new ArrayCollection();
        $this->tournaments = new ArrayCollection();
        $this->leaderboards = new ArrayCollection();
        $this->tournamentsScores = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getRolesNames(): array
    {
        $roles = $this->roles;

        foreach ($roles as $key => $role) {
            switch ($role) {
                case 'ROLE_SUPER_ADMIN':
                    $roles[$key] = 'Správce webu';
                    break;
                case 'ROLE_ADMIN':
                    $roles[$key] = 'Admin';
                    break;
                case 'ROLE_USER':
                    $roles[$key] = 'Uživatel';
                    break;
                default:
                    $roles[] = 'Uživatel';
                    break;
            }
        }

        $roles[] = 'Uživatel';

        return array_unique($roles);
    }

    /**
     * This method can be removed in Symfony 6.0 - is not needed for apps that do not check user passwords.
     *
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * This method can be removed in Symfony 6.0 - is not needed for apps that do not check user passwords.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return Collection|ApiToken[]
     */
    public function getApiTokens(): Collection
    {
        return $this->apiTokens;
    }

    public function addApiToken(ApiToken $apiToken): self
    {
        if (!$this->apiTokens->contains($apiToken)) {
            $this->apiTokens[] = $apiToken;
            $apiToken->setUser($this);
        }

        return $this;
    }

    public function removeApiToken(ApiToken $apiToken): self
    {
        if ($this->apiTokens->removeElement($apiToken)) {
            // set the owning side to null (unless already changed)
            if ($apiToken->getUser() === $this) {
                $apiToken->setUser(null);
            }
        }

        return $this;
    }

    public function __toString() {

        return $this->getFirstName();

    }

    /**
     * @return Collection|Log[]
     */
    public function getLogs(): Collection
    {
        return $this->logs;
    }

    public function addLog(Log $log): self
    {
        if (!$this->logs->contains($log)) {
            $this->logs[] = $log;
            $log->setUser($this);
        }

        return $this;
    }

    public function removeLog(Log $log): self
    {
        if ($this->logs->removeElement($log)) {
            // set the owning side to null (unless already changed)
            if ($log->getUser() === $this) {
                $log->setUser(null);
            }
        }

        return $this;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname): self
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function getUserIp(): ?string
    {
        return $this->user_ip;
    }

    public function setUserIp(?string $user_ip): self
    {
        $this->user_ip = $user_ip;

        return $this;
    }

    public function getGdpr(): ?int
    {
        return $this->gdpr;
    }

    public function setGdpr(int $gdpr): self
    {
        $this->gdpr = $gdpr;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|FormAnswers[]
     */
    public function getFormAnswers(): Collection
    {
        return $this->formAnswers;
    }

    public function addFormAnswer(FormAnswers $formAnswer): self
    {
        if (!$this->formAnswers->contains($formAnswer)) {
            $this->formAnswers[] = $formAnswer;
            $formAnswer->setUser($this);
        }

        return $this;
    }

    public function removeFormAnswer(FormAnswers $formAnswer): self
    {
        if ($this->formAnswers->removeElement($formAnswer)) {
            // set the owning side to null (unless already changed)
            if ($formAnswer->getUser() === $this) {
                $formAnswer->setUser(null);
            }
        }

        return $this;
    }

    public function getCountry(): ?Countries
    {
        return $this->country;
    }

    public function setCountry(?Countries $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getPasswordRepeat(): ?string
    {
        return $this->passwordRepeat;
    }

    public function setPasswordRepeat(?string $passwordRepeat): self
    {
        $this->passwordRepeat = $passwordRepeat;

        return $this;
    }

    /**
     * @return Collection|News[]
     */
    public function getNews(): Collection
    {
        return $this->news;
    }

    public function addNews(News $news): self
    {
        if (!$this->news->contains($news)) {
            $this->news[] = $news;
            $news->setAuthor($this);
        }

        return $this;
    }

    public function removeNews(News $news): self
    {
        if ($this->news->removeElement($news)) {
            // set the owning side to null (unless already changed)
            if ($news->getAuthor() === $this) {
                $news->setAuthor(null);
            }
        }

        return $this;
    }

    public function getUniqueId(): ?string
    {
        return $this->uniqueId;
    }

    public function setUniqueId(string $uniqueId): self
    {
        $this->uniqueId = $uniqueId;

        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(\DateTimeInterface $birthdate): self
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function getCoins(): ?int
    {
        return $this->coins;
    }

    public function setCoins(?int $coins): self
    {
        $this->coins = $coins;

        return $this;
    }

    public function getScoresaberId(): ?int
    {
        return $this->scoresaberId;
    }

    public function setScoresaberId(?int $scoresaberId): self
    {
        $this->scoresaberId = $scoresaberId;

        return $this;
    }

    public function getScoresaberLink(): ?string
    {
        return $this->scoresaberLink;
    }

    public function setScoresaberLink(?string $scoresaberLink): self
    {
        $this->scoresaberLink = $scoresaberLink;

        return $this;
    }

    public function getTwitchLink(): ?string
    {
        return $this->twitchLink;
    }

    public function setTwitchLink(?string $twitchLink): self
    {
        $this->twitchLink = $twitchLink;

        return $this;
    }

    public function getDiscordNickname(): ?string
    {
        return $this->discordNickname;
    }

    public function setDiscordNickname(?string $discordNickname): self
    {
        $this->discordNickname = $discordNickname;

        return $this;
    }

    public function getDonate(): ?int
    {
        return $this->donate;
    }

    public function setDonate(?int $donate): self
    {
        $this->donate = $donate;

        return $this;
    }

    public function getLoggedAt(): ?\DateTimeInterface
    {
        return $this->loggedAt;
    }

    public function setLaggedAt(?\DateTimeInterface $loggedAt): self
    {
        $this->loggedAt = $loggedAt;

        return $this;
    }

    public function getImgName(): ?string
    {
        return $this->imgName;
    }

    public function setImgName(?string $imgName): self
    {
        $this->imgName = $imgName;

        return $this;
    }

    public function getImgPath($index = null): ?string
    {
        if (null !== $index && '' !== $index) {
            if (file_exists('uploads/users/'.$index)) {
                return 'uploads/users/'.$index;
            } else if (file_exists('build/images/users/'.$index)) {
                return 'build/images/users/'.$index;
            }
        }

        return 'build/images/utilities/profile-empty-background.jpg';
    }

    /**
     * @return Collection|UserBadges[]
     */
    public function getBadge(): Collection
    {
        return $this->badge;
    }

    public function addBadge(UserBadges $badge): self
    {
        if (!$this->badge->contains($badge)) {
            $this->badge[] = $badge;
        }

        return $this;
    }

    public function removeBadge(UserBadges $badge): self
    {
        $this->badge->removeElement($badge);

        return $this;
    }

    public function getTwitchNickname(): ?string
    {
        return $this->twitchNickname;
    }

    public function setTwitchNickname(?string $twitchNickname): self
    {
        $this->twitchNickname = $twitchNickname;

        return $this;
    }

    public function getHdm(): ?Hdm
    {
        return $this->hdm;
    }

    public function setHdm(?Hdm $hdm): self
    {
        $this->hdm = $hdm;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return Collection|Tournaments[]
     */
    public function getTournaments(): Collection
    {
        return $this->tournaments;
    }

    public function addTournament(Tournaments $tournament): self
    {
        if (!$this->tournaments->contains($tournament)) {
            $this->tournaments[] = $tournament;
            $tournament->addPlayer($this);
        }

        return $this;
    }

    public function removeTournament(Tournaments $tournament): self
    {
        if ($this->tournaments->removeElement($tournament)) {
            $tournament->removePlayer($this);
        }

        return $this;
    }

    public function getActive(): ?int
    {
        return $this->active;
    }

    public function setActive(int $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function hide(): self
    {
        $this->active = 0;

        return $this;
    }

    public function show(): self
    {
        $this->active = 1;

        return $this;
    }

    /**
     * @return Collection|Leaderboard[]
     */
    public function getLeaderboards(): Collection
    {
        return $this->leaderboards;
    }

    public function addLeaderboard(Leaderboard $leaderboard): self
    {
        if (!$this->leaderboards->contains($leaderboard)) {
            $this->leaderboards[] = $leaderboard;
            $leaderboard->setUser($this);
        }

        return $this;
    }

    public function removeLeaderboard(Leaderboard $leaderboard): self
    {
        if ($this->leaderboards->removeElement($leaderboard)) {
            // set the owning side to null (unless already changed)
            if ($leaderboard->getUser() === $this) {
                $leaderboard->setUser(null);
            }
        }

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return Collection|TournamentsScores[]
     */
    public function getTournamentsScores(): Collection
    {
        return $this->tournamentsScores;
    }

    public function addTournamentsScore(TournamentsScores $tournamentsScore): self
    {
        if (!$this->tournamentsScores->contains($tournamentsScore)) {
            $this->tournamentsScores[] = $tournamentsScore;
            $tournamentsScore->setUser($this);
        }

        return $this;
    }

    public function removeTournamentsScore(TournamentsScores $tournamentsScore): self
    {
        if ($this->tournamentsScores->removeElement($tournamentsScore)) {
            // set the owning side to null (unless already changed)
            if ($tournamentsScore->getUser() === $this) {
                $tournamentsScore->setUser(null);
            }
        }

        return $this;
    }

}
